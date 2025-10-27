"""
Locust load test for Argus PAM
Converted from k6-test.js

Run with: locust -f locust.py --host https://argus.pam
"""

import json
import random
from locust import HttpUser, task, between
from locust import LoadTestShape


class StagesShape(LoadTestShape):
    """
    Custom load shape to match k6 stages:
    - Ramp up to 20 users over 1 minute
    - Maintain 20 users for 3.5 minutes
    - Ramp down to 0 over 1 minute
    """
    stages = [
        {"duration": 60, "users": 20, "spawn_rate": 20},     # Ramp up to 20 over 1min
        {"duration": 270, "users": 20, "spawn_rate": 20},    # Stay at 20 for 3.5min (210s + 60s)
        {"duration": 330, "users": 0, "spawn_rate": 20},     # Ramp down to 0 over 1min (270s + 60s)
    ]

    def tick(self):
        run_time = self.get_run_time()

        for stage in self.stages:
            if run_time < stage["duration"]:
                tick_data = (stage["users"], stage["spawn_rate"])
                return tick_data

        return None


class ArgusPAMUser(HttpUser):
    """
    Simulates a user interacting with Argus PAM:
    - Login with credentials
    - 2FA authentication
    - Organization switching
    - Creating asset requests
    - Browsing various resources
    - Logout
    """

    # Wait between 1-2 seconds between tasks (matching k6's sleep)
    wait_time = between(1, 2)

    # Base URL is set via --host parameter when running locust

    def on_start(self):
        """Called when a simulated user starts"""
        self.client.verify = False  # Disable SSL verification if needed

    @task
    def user_flow(self):
        """Complete user flow matching the k6 test"""

        # 1. GET homepage
        with self.client.get(
            "/",
            headers={
                "Connection": "keep-alive",
                "sec-ch-ua": '"Google Chrome";v="141", "Not?A_Brand";v="8", "Chromium";v="141"',
                "sec-ch-ua-mobile": "?0",
                "sec-ch-ua-platform": '"macOS"',
                "Upgrade-Insecure-Requests": "1",
                "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7",
                "Sec-Fetch-Site": "none",
                "Sec-Fetch-Mode": "navigate",
                "Sec-Fetch-User": "?1",
                "Sec-Fetch-Dest": "document",
                "Accept-Encoding": "gzip, deflate, br, zstd",
                "Accept-Language": "en-GB,en-US;q=0.9,en;q=0.8",
            },
            catch_response=True,
        ) as response:
            if response.status_code == 200:
                response.success()
            else:
                response.failure(f"Homepage returned {response.status_code}")

        # 2. POST login
        with self.client.post(
            "/auth/login",
            json={
                "email": "heanluen@gmail.com",
                "password": "password",
                "__superform_id": "1p3ituu"
            },
            headers={
                "Connection": "keep-alive",
                "sec-ch-ua-platform": '"macOS"',
                "accept": "application/json",
                "sec-ch-ua": '"Google Chrome";v="141", "Not?A_Brand";v="8", "Chromium";v="141"',
                "content-type": "application/x-www-form-urlencoded",
                "sec-ch-ua-mobile": "?0",
                "x-sveltekit-action": "true",
                "Origin": "https://argus.pam",
                "Sec-Fetch-Site": "same-origin",
                "Sec-Fetch-Mode": "cors",
                "Sec-Fetch-Dest": "empty",
                "Referer": "https://argus.pam/auth/login",
                "Accept-Encoding": "gzip, deflate, br, zstd",
                "Accept-Language": "en-GB,en-US;q=0.9,en;q=0.8",
            },
            catch_response=True,
        ) as response:
            if response.status_code == 200:
                response.success()
            else:
                response.failure(f"Login returned {response.status_code}")

        # 3. POST 2FA
        with self.client.post(
            "/auth/2fa",
            json={
                "temp_key": "jKZkdv1jFn75iqz4X9PQkU3tnXrgIF5Q",
                "code": "585246",
                "__superform_id": "87tvg0"
            },
            headers={
                "Connection": "keep-alive",
                "sec-ch-ua-platform": '"macOS"',
                "accept": "application/json",
                "sec-ch-ua": '"Google Chrome";v="141", "Not?A_Brand";v="8", "Chromium";v="141"',
                "content-type": "application/x-www-form-urlencoded",
                "sec-ch-ua-mobile": "?0",
                "x-sveltekit-action": "true",
                "Origin": "https://argus.pam",
                "Sec-Fetch-Site": "same-origin",
                "Sec-Fetch-Mode": "cors",
                "Sec-Fetch-Dest": "empty",
                "Referer": "https://argus.pam/auth/2fa",
                "Accept-Encoding": "gzip, deflate, br, zstd",
                "Accept-Language": "en-GB,en-US;q=0.9,en;q=0.8",
            },
            catch_response=True,
        ) as response:
            if response.status_code == 200:
                response.success()
            else:
                response.failure(f"2FA returned {response.status_code}")

        # 4. POST org switch to org 2
        with self.client.post(
            "/api/org/switch",
            data='{"orgId":2}',
            headers={
                "Connection": "keep-alive",
                "sec-ch-ua-platform": '"macOS"',
                "sec-ch-ua": '"Google Chrome";v="141", "Not?A_Brand";v="8", "Chromium";v="141"',
                "Content-Type": "text/plain;charset=UTF-8",
                "sec-ch-ua-mobile": "?0",
                "Accept": "*/*",
                "Origin": "https://argus.pam",
                "Sec-Fetch-Site": "same-origin",
                "Sec-Fetch-Mode": "cors",
                "Sec-Fetch-Dest": "empty",
                "Referer": "https://argus.pam/dashboard",
                "Accept-Encoding": "gzip, deflate, br, zstd",
                "Accept-Language": "en-GB,en-US;q=0.9,en;q=0.8",
            },
            catch_response=True,
        ) as response:
            if response.status_code == 200:
                response.success()
            else:
                response.failure(f"Org switch to 2 returned {response.status_code}")

        # 5. POST org switch to org 3
        with self.client.post(
            "/api/org/switch",
            data='{"orgId":3}',
            headers={
                "Connection": "keep-alive",
                "sec-ch-ua-platform": '"macOS"',
                "sec-ch-ua": '"Google Chrome";v="141", "Not?A_Brand";v="8", "Chromium";v="141"',
                "Content-Type": "text/plain;charset=UTF-8",
                "sec-ch-ua-mobile": "?0",
                "Accept": "*/*",
                "Origin": "https://argus.pam",
                "Sec-Fetch-Site": "same-origin",
                "Sec-Fetch-Mode": "cors",
                "Sec-Fetch-Dest": "empty",
                "Referer": "https://argus.pam/dashboard",
                "Accept-Encoding": "gzip, deflate, br, zstd",
                "Accept-Language": "en-GB,en-US;q=0.9,en;q=0.8",
            },
            catch_response=True,
        ) as response:
            if response.status_code == 200:
                response.success()
            else:
                response.failure(f"Org switch to 3 returned {response.status_code}")

        # 6. POST org switch to org 1
        with self.client.post(
            "/api/org/switch",
            data='{"orgId":1}',
            headers={
                "Connection": "keep-alive",
                "sec-ch-ua-platform": '"macOS"',
                "sec-ch-ua": '"Google Chrome";v="141", "Not?A_Brand";v="8", "Chromium";v="141"',
                "Content-Type": "text/plain;charset=UTF-8",
                "sec-ch-ua-mobile": "?0",
                "Accept": "*/*",
                "Origin": "https://argus.pam",
                "Sec-Fetch-Site": "same-origin",
                "Sec-Fetch-Mode": "cors",
                "Sec-Fetch-Dest": "empty",
                "Referer": "https://argus.pam/dashboard",
                "Accept-Encoding": "gzip, deflate, br, zstd",
                "Accept-Language": "en-GB,en-US;q=0.9,en;q=0.8",
            },
            catch_response=True,
        ) as response:
            if response.status_code == 200:
                response.success()
            else:
                response.failure(f"Org switch to 1 returned {response.status_code}")

        # 7. POST create asset request
        with self.client.post(
            "/requests/assets",
            json={
                "asset_id": "27",
                "org_id": "1",
                "start_datetime": "Tue Oct 21 2025 17:45:00 GMT+0800 (Singapore Standard Time)",
                "end_datetime": "Thu Oct 30 2025 17:45:00 GMT+0800 (Singapore Standard Time)",
                "reason": "qwe",
                "intended_query": "qwe",
                "scope": "ReadOnly",
                "__superform_id": "10e8w1p"
            },
            headers={
                "Connection": "keep-alive",
                "sec-ch-ua-platform": '"macOS"',
                "accept": "application/json",
                "sec-ch-ua": '"Google Chrome";v="141", "Not?A_Brand";v="8", "Chromium";v="141"',
                "content-type": "application/x-www-form-urlencoded",
                "sec-ch-ua-mobile": "?0",
                "x-sveltekit-action": "true",
                "Origin": "https://argus.pam",
                "Sec-Fetch-Site": "same-origin",
                "Sec-Fetch-Mode": "cors",
                "Sec-Fetch-Dest": "empty",
                "Referer": "https://argus.pam/requests/assets",
                "Accept-Encoding": "gzip, deflate, br, zstd",
                "Accept-Language": "en-GB,en-US;q=0.9,en;q=0.8",
            },
            catch_response=True,
        ) as response:
            if response.status_code == 200:
                response.success()
            else:
                response.failure(f"Create asset request returned {response.status_code}")

        # 8. GET search requests
        with self.client.get(
            "/api/search/requests?page=1&sort=-created_at&include=asset%2Crequester%2Capprover%2Crejecter",
            headers={
                "Connection": "keep-alive",
                "sec-ch-ua-platform": '"macOS"',
                "sec-ch-ua": '"Google Chrome";v="141", "Not?A_Brand";v="8", "Chromium";v="141"',
                "sec-ch-ua-mobile": "?0",
                "Accept": "*/*",
                "Sec-Fetch-Site": "same-origin",
                "Sec-Fetch-Mode": "cors",
                "Sec-Fetch-Dest": "empty",
                "Referer": "https://argus.pam/requests",
                "Accept-Encoding": "gzip, deflate, br, zstd",
                "Accept-Language": "en-GB,en-US;q=0.9,en;q=0.8",
            },
            catch_response=True,
        ) as response:
            if response.status_code == 200:
                response.success()
            else:
                response.failure(f"Search requests returned {response.status_code}")

        # 9. GET search assets
        with self.client.get(
            "/api/search/assets?page=1",
            headers={
                "Connection": "keep-alive",
                "sec-ch-ua-platform": '"macOS"',
                "sec-ch-ua": '"Google Chrome";v="141", "Not?A_Brand";v="8", "Chromium";v="141"',
                "sec-ch-ua-mobile": "?0",
                "Accept": "*/*",
                "Sec-Fetch-Site": "same-origin",
                "Sec-Fetch-Mode": "cors",
                "Sec-Fetch-Dest": "empty",
                "Referer": "https://argus.pam/assets",
                "Accept-Encoding": "gzip, deflate, br, zstd",
                "Accept-Language": "en-GB,en-US;q=0.9,en;q=0.8",
            },
            catch_response=True,
        ) as response:
            if response.status_code == 200:
                response.success()
            else:
                response.failure(f"Search assets returned {response.status_code}")

        # 10. GET search orgs
        with self.client.get(
            "/api/search/orgs?page=1",
            headers={
                "Connection": "keep-alive",
                "sec-ch-ua-platform": '"macOS"',
                "sec-ch-ua": '"Google Chrome";v="141", "Not?A_Brand";v="8", "Chromium";v="141"',
                "sec-ch-ua-mobile": "?0",
                "Accept": "*/*",
                "Sec-Fetch-Site": "same-origin",
                "Sec-Fetch-Mode": "cors",
                "Sec-Fetch-Dest": "empty",
                "Referer": "https://argus.pam/organizations",
                "Accept-Encoding": "gzip, deflate, br, zstd",
                "Accept-Language": "en-GB,en-US;q=0.9,en;q=0.8",
            },
            catch_response=True,
        ) as response:
            if response.status_code == 200:
                response.success()
            else:
                response.failure(f"Search orgs returned {response.status_code}")

        # 11. GET search user-groups
        with self.client.get(
            "/api/search/user-groups?page=1&count=users",
            headers={
                "Connection": "keep-alive",
                "sec-ch-ua-platform": '"macOS"',
                "sec-ch-ua": '"Google Chrome";v="141", "Not?A_Brand";v="8", "Chromium";v="141"',
                "sec-ch-ua-mobile": "?0",
                "Accept": "*/*",
                "Sec-Fetch-Site": "same-origin",
                "Sec-Fetch-Mode": "cors",
                "Sec-Fetch-Dest": "empty",
                "Referer": "https://argus.pam/organizations/user-groups",
                "Accept-Encoding": "gzip, deflate, br, zstd",
                "Accept-Language": "en-GB,en-US;q=0.9,en;q=0.8",
            },
            catch_response=True,
        ) as response:
            if response.status_code == 200:
                response.success()
            else:
                response.failure(f"Search user-groups returned {response.status_code}")

        # 12. GET search users
        with self.client.get(
            "/api/search/users?page=1",
            headers={
                "Connection": "keep-alive",
                "sec-ch-ua-platform": '"macOS"',
                "sec-ch-ua": '"Google Chrome";v="141", "Not?A_Brand";v="8", "Chromium";v="141"',
                "sec-ch-ua-mobile": "?0",
                "Accept": "*/*",
                "Sec-Fetch-Site": "same-origin",
                "Sec-Fetch-Mode": "cors",
                "Sec-Fetch-Dest": "empty",
                "Referer": "https://argus.pam/users",
                "Accept-Encoding": "gzip, deflate, br, zstd",
                "Accept-Language": "en-GB,en-US;q=0.9,en;q=0.8",
            },
            catch_response=True,
        ) as response:
            if response.status_code == 200:
                response.success()
            else:
                response.failure(f"Search users returned {response.status_code}")

        # 13. GET search roles
        with self.client.get(
            "/api/search/roles?page=1",
            headers={
                "Connection": "keep-alive",
                "sec-ch-ua-platform": '"macOS"',
                "sec-ch-ua": '"Google Chrome";v="141", "Not?A_Brand";v="8", "Chromium";v="141"',
                "sec-ch-ua-mobile": "?0",
                "Accept": "*/*",
                "Sec-Fetch-Site": "same-origin",
                "Sec-Fetch-Mode": "cors",
                "Sec-Fetch-Dest": "empty",
                "Referer": "https://argus.pam/users/roles",
                "Accept-Encoding": "gzip, deflate, br, zstd",
                "Accept-Language": "en-GB,en-US;q=0.9,en;q=0.8",
            },
            catch_response=True,
        ) as response:
            if response.status_code == 200:
                response.success()
            else:
                response.failure(f"Search roles returned {response.status_code}")

        # 14. GET search permissions page 1
        with self.client.get(
            "/api/search/permissions?page=1",
            headers={
                "Connection": "keep-alive",
                "sec-ch-ua-platform": '"macOS"',
                "sec-ch-ua": '"Google Chrome";v="141", "Not?A_Brand";v="8", "Chromium";v="141"',
                "sec-ch-ua-mobile": "?0",
                "Accept": "*/*",
                "Sec-Fetch-Site": "same-origin",
                "Sec-Fetch-Mode": "cors",
                "Sec-Fetch-Dest": "empty",
                "Referer": "https://argus.pam/users/permissions",
                "Accept-Encoding": "gzip, deflate, br, zstd",
                "Accept-Language": "en-GB,en-US;q=0.9,en;q=0.8",
            },
            catch_response=True,
        ) as response:
            if response.status_code == 200:
                response.success()
            else:
                response.failure(f"Search permissions page 1 returned {response.status_code}")

        # 15. GET search permissions page 2
        with self.client.get(
            "/api/search/permissions?page=2",
            headers={
                "Connection": "keep-alive",
                "sec-ch-ua-platform": '"macOS"',
                "sec-ch-ua": '"Google Chrome";v="141", "Not?A_Brand";v="8", "Chromium";v="141"',
                "sec-ch-ua-mobile": "?0",
                "Accept": "*/*",
                "Sec-Fetch-Site": "same-origin",
                "Sec-Fetch-Mode": "cors",
                "Sec-Fetch-Dest": "empty",
                "Referer": "https://argus.pam/users/permissions?page=1",
                "Accept-Encoding": "gzip, deflate, br, zstd",
                "Accept-Language": "en-GB,en-US;q=0.9,en;q=0.8",
            },
            catch_response=True,
        ) as response:
            if response.status_code == 200:
                response.success()
            else:
                response.failure(f"Search permissions page 2 returned {response.status_code}")

        # 16. POST logout
        with self.client.post(
            "/auth/logout",
            headers={
                "Connection": "keep-alive",
                "Cache-Control": "max-age=0",
                "sec-ch-ua": '"Google Chrome";v="141", "Not?A_Brand";v="8", "Chromium";v="141"',
                "sec-ch-ua-mobile": "?0",
                "sec-ch-ua-platform": '"macOS"',
                "Origin": "https://argus.pam",
                "Content-Type": "application/x-www-form-urlencoded",
                "Upgrade-Insecure-Requests": "1",
                "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7",
                "Sec-Fetch-Site": "same-origin",
                "Sec-Fetch-Mode": "navigate",
                "Sec-Fetch-User": "?1",
                "Sec-Fetch-Dest": "document",
                "Referer": "https://argus.pam/settings/security",
                "Accept-Encoding": "gzip, deflate, br, zstd",
                "Accept-Language": "en-GB,en-US;q=0.9,en;q=0.8",
            },
            catch_response=True,
        ) as response:
            if response.status_code == 200:
                response.success()
            else:
                response.failure(f"Logout returned {response.status_code}")
