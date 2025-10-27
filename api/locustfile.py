"""
Locust load test for Argus PAM API
Tests the REST API endpoints directly

Run with: locust -f locust.py --host https://api.argus.pam
Note: This application has no /api prefix in routes (apiPrefix: '' in bootstrap/app.php)
"""

import json
import random
import logging
from locust import HttpUser, task, between
from locust import LoadTestShape

# Configure logging
logger = logging.getLogger(__name__)


class StagesShape(LoadTestShape):
    """
    Custom load shape to match k6 stages:
    - Ramp up to 50 users over 1 minute
    - Maintain 50 users for 3 minutes
    - Ramp down to 0 over 1 minute
    """
    stages = [
        {"duration": 60, "users": 250, "spawn_rate": 5},
        {"duration": 120, "users": 250, "spawn_rate": 15},
        {"duration": 180, "users": 0, "spawn_rate": 5},
    ]

    def tick(self):
        run_time = self.get_run_time()
        for stage in self.stages:
            if run_time < stage["duration"]:
                return (stage["users"], stage["spawn_rate"])
        return None


class ArgusPAMAPIUser(HttpUser):
    """
    Simulates a user interacting with Argus PAM API.

    Authentication Flow:
    1. Login with credentials → temp_key
    2. Verify 2FA → auth token
    3. Fetch organization ID → org_id
    4. Execute tasks (only if authenticated)
    5. Logout on stop
    """

    wait_time = between(1, 2)

    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self.token = None
        self.org_id = None
        self.temp_key = None
        self.authenticated = False
        self.asset_ids = []  # Cache valid asset IDs
        self.requires_2fa = True

    def on_start(self):
        """
        Initialize user session with full authentication flow.
        If any step fails, stop the user from executing tasks.
        """
        self.client.verify = False  # Disable SSL verification for self-signed certs

        logger.info("Starting authentication flow...")

        # Step 1: Login
        if not self._login():
            logger.error("Authentication failed at step 1: Login")
            self.environment.runner.quit()
            return

        # Step 2: 2FA Verification (only if token not provided by login)
        if self.token is None:
            if not self._verify_2fa():
                logger.error("Authentication failed at step 2: 2FA Verification")
                self.environment.runner.quit()
                return

        # Step 3: Fetch Organization ID
        if not self._fetch_org_id():
            logger.error("Authentication failed at step 3: Fetch Organization ID")
            self.environment.runner.quit()
            return

        # Authentication successful
        self.authenticated = True
        logger.info(f"Authentication successful! User ready with org_id={self.org_id}")

    def _login(self):
        """Step 1: Authenticate and get temporary key"""
        with self.client.post(
            "/auth/login",
            json={
                "email": "admin@admin.com",
                "password": "password"
            },
            headers=self._get_base_headers(),
            name="1. POST /auth/login",
            catch_response=True
        ) as response:
            if response.status_code != 200:
                response.failure(f"Login failed with HTTP {response.status_code}")
                return False

            try:
                data = response.json().get("data", {})

                # Handle case where 2FA is not required and token is already issued
                self.requires_2fa = data.get("requires_2fa", True)
                token = data.get("token")
                if self.requires_2fa is False and token:
                    self.token = token
                    response.success()
                    return True

                # Otherwise expect a temp_key for the 2FA step
                self.temp_key = data.get("temp_key")

                if not self.temp_key:
                    response.failure("No temp_key in login response")
                    return False

                response.success()
                return True

            except Exception as e:
                response.failure(f"Failed to parse login response: {e}")
                return False

    def _verify_2fa(self):
        """Step 2: Verify 2FA and get authentication token"""
        with self.client.post(
            "/auth/2fa",
            json={
                "temp_key": self.temp_key,
                "code": "123456"  # Mock code - replace with valid TOTP in production
            },
            headers=self._get_base_headers(),
            name="2. POST /auth/2fa",
            catch_response=True
        ) as response:
            if response.status_code != 200:
                response.failure(f"2FA failed with HTTP {response.status_code}")
                return False

            try:
                data = response.json().get("data", {})
                self.token = data.get("token")

                if not self.token:
                    response.failure("No token in 2FA response")
                    return False

                response.success()
                return True

            except Exception as e:
                response.failure(f"Failed to parse 2FA response: {e}")
                return False

    def _fetch_org_id(self):
        """Step 3: Fetch user's organization ID"""
        with self.client.get(
            "/users/me/orgs",
            headers=self._get_auth_headers(),
            name="3. GET /users/me/orgs (fetch org_id)",
            catch_response=True
        ) as response:
            if response.status_code != 200:
                response.failure(f"Fetch orgs failed with HTTP {response.status_code}")
                return False

            try:
                data = response.json().get("data", [])

                if not data:
                    response.failure("No organizations found in response")
                    return False

                # Extract org_id from attributes or direct field
                org = data[0]
                self.org_id = org.get("attributes", {}).get("id") or org.get("id")

                if not self.org_id:
                    response.failure(f"No org_id found in organization data: {org}")
                    return False

                response.success()
                return True

            except Exception as e:
                response.failure(f"Failed to parse organizations response: {e}")
                return False

    def _get_base_headers(self):
        """Get base headers for all requests"""
        return {
            "Accept": "application/json",
            "Content-Type": "application/json"
        }

    def _get_auth_headers(self):
        """Get headers with authentication token and org_id"""
        headers = self._get_base_headers()

        if self.token:
            headers["Authorization"] = f"Bearer {self.token}"

        if self.org_id is not None:
            headers["x-organization-id"] = str(self.org_id)

        return headers

    def _check_auth(self):
        """Check if user is authenticated before executing tasks"""
        if not self.authenticated:
            logger.warning("Task attempted before authentication completed")
        return self.authenticated

    # ========================================================================
    # TASK METHODS
    # ========================================================================

    @task(3)
    def get_dashboard(self):
        """Get dashboard data"""
        if not self._check_auth():
            return

        self.client.get(
            "/dashboard",
            headers=self._get_auth_headers(),
            name="4. GET /dashboard"
        )

    @task(5)
    def list_requests(self):
        """Get list of access requests with filters and includes"""
        if not self._check_auth():
            return

        self.client.get(
            "/requests?page=1&sort=-created_at&include=asset,requester,approver,rejecter",
            headers=self._get_auth_headers(),
            name="5. GET /requests"
        )

    @task(4)
    def list_assets(self):
        """Get list of assets and cache their IDs"""
        if not self._check_auth():
            return

        response = self.client.get(
            "/assets?page=1&sort=name",
            headers=self._get_auth_headers(),
            name="6. GET /assets"
        )

        # Cache asset IDs for view_asset task
        if response.status_code == 200:
            try:
                data = response.json().get("data", [])
                if data:
                    # Extract IDs from the response
                    self.asset_ids = [
                        asset.get("attributes", {}).get("id") or asset.get("id")
                        for asset in data
                        if asset.get("attributes", {}).get("id") or asset.get("id")
                    ]
            except Exception:
                pass  # Silently ignore parsing errors

    @task(2)
    def view_asset(self):
        """View a specific asset using cached IDs"""
        if not self._check_auth():
            return

        # Use cached asset IDs if available, otherwise skip
        if not self.asset_ids:
            return

        asset_id = random.choice(self.asset_ids)
        self.client.get(
            f"/assets/{asset_id}",
            headers=self._get_auth_headers(),
            name="7. GET /assets/:id"
        )

    @task(3)
    def list_users(self):
        """Get list of users"""
        if not self._check_auth():
            return

        self.client.get(
            "/users?page=1&sort=name",
            headers=self._get_auth_headers(),
            name="8. GET /users"
        )

    @task(2)
    def get_user_me(self):
        """Get current user info"""
        if not self._check_auth():
            return

        self.client.get(
            "/users/me",
            headers=self._get_auth_headers(),
            name="9. GET /users/me"
        )

    @task(2)
    def list_user_orgs(self):
        """Get user's organizations"""
        if not self._check_auth():
            return

        self.client.get(
            "/users/me/orgs",
            headers=self._get_auth_headers(),
            name="10. GET /users/me/orgs"
        )

    @task(3)
    def list_orgs(self):
        """Get list of organizations"""
        if not self._check_auth():
            return

        self.client.get(
            "/orgs?page=1",
            headers=self._get_auth_headers(),
            name="11. GET /orgs"
        )

    @task(2)
    def list_user_groups(self):
        """Get list of user groups"""
        if not self._check_auth():
            return

        self.client.get(
            "/user-groups?page=1&count=users",
            headers=self._get_auth_headers(),
            name="12. GET /user-groups"
        )

    @task(2)
    def list_roles(self):
        """Get list of roles"""
        if not self._check_auth():
            return

        self.client.get(
            "/roles?page=1",
            headers=self._get_auth_headers(),
            name="13. GET /roles"
        )

    @task(2)
    def list_permissions(self):
        """Get list of permissions with pagination"""
        if not self._check_auth():
            return

        page = random.randint(1, 3)
        self.client.get(
            f"/permissions?page={page}",
            headers=self._get_auth_headers(),
            name="14. GET /permissions"
        )

    # @task(1)
    # def list_sessions(self):
    #     """Get list of sessions"""
    #     if not self._check_auth():
    #         return

    #     self.client.get(
    #         "/sessions?page=1&sort=-created_at",
    #         headers=self._get_auth_headers(),
    #         name="15. GET /sessions"
    #     )

    @task(1)
    def get_settings(self):
        """Get application settings"""
        if not self._check_auth():
            return

        self.client.get(
            "/settings",
            headers=self._get_auth_headers(),
            name="16. GET /settings"
        )

    @task(1)
    def search_multiple_resources(self):
        """Simulate a user browsing through multiple resource pages"""
        if not self._check_auth():
            return

        resources = [
            ("/requests?page=1&sort=-created_at", "Search Requests"),
            ("/assets?page=1", "Search Assets"),
            ("/users?page=1", "Search Users"),
            ("/roles?page=1", "Search Roles"),
        ]

        for endpoint, name in resources:
            self.client.get(
                endpoint,
                headers=self._get_auth_headers(),
                name=f"17. {name}"
            )

    def on_stop(self):
        """Called when a simulated user stops - perform logout"""
        if self.token and self.authenticated:
            self.client.post(
                "/auth/logout",
                headers=self._get_auth_headers(),
                name="18. POST /auth/logout"
            )
            logger.info("User logged out successfully")
