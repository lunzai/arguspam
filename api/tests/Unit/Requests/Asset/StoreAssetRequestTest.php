<?php

namespace Tests\Unit\Requests\Asset;

use App\Enums\Dbms;
use App\Enums\Status;
use App\Http\Requests\Asset\StoreAssetRequest;
use App\Models\Org;
use App\Services\Jit\Secrets\SecretsManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Mockery;
use Tests\TestCase;

class StoreAssetRequestTest extends TestCase
{
    use RefreshDatabase;

    protected Org $org;

    protected SecretsManager $secretsManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Org::factory()->create();
        $this->secretsManager = Mockery::mock(SecretsManager::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_authorizes_all_requests(): void
    {
        // Arrange
        $request = new StoreAssetRequest($this->secretsManager);

        // Act
        $result = $request->authorize();

        // Assert
        $this->assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_with_all_required_fields(): void
    {
        // Arrange
        $data = [
            'org_id' => $this->org->id,
            'name' => 'Production DB',
            'description' => 'Main production database',
            'status' => Status::ACTIVE->value,
            'host' => 'db.example.com',
            'port' => 5432,
            'dbms' => Dbms::POSTGRESQL->value,
            'username' => 'admin',
            'password' => 'secret123',
        ];

        $request = new StoreAssetRequest($this->secretsManager);
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_org_id_missing(): void
    {
        // Arrange
        $data = [
            'name' => 'Test DB',
            'status' => Status::ACTIVE->value,
            'host' => 'localhost',
            'port' => 5432,
            'dbms' => Dbms::POSTGRESQL->value,
            'username' => 'admin',
            'password' => 'secret',
        ];

        $request = new StoreAssetRequest($this->secretsManager);
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('org_id', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_org_id_not_exists(): void
    {
        // Arrange
        $data = [
            'org_id' => 99999,
            'name' => 'Test DB',
            'status' => Status::ACTIVE->value,
            'host' => 'localhost',
            'port' => 5432,
            'dbms' => Dbms::POSTGRESQL->value,
            'username' => 'admin',
            'password' => 'secret',
        ];

        $request = new StoreAssetRequest($this->secretsManager);
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('org_id', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_name_within_length_constraints(): void
    {
        // Arrange
        $data = [
            'org_id' => $this->org->id,
            'name' => 'DB', // Min 2 characters
            'status' => Status::ACTIVE->value,
            'host' => 'localhost',
            'port' => 5432,
            'dbms' => Dbms::POSTGRESQL->value,
            'username' => 'admin',
            'password' => 'secret',
        ];

        $request = new StoreAssetRequest($this->secretsManager);
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_name_too_short(): void
    {
        // Arrange
        $data = [
            'org_id' => $this->org->id,
            'name' => 'D', // Only 1 character
            'status' => Status::ACTIVE->value,
            'host' => 'localhost',
            'port' => 5432,
            'dbms' => Dbms::POSTGRESQL->value,
            'username' => 'admin',
            'password' => 'secret',
        ];

        $request = new StoreAssetRequest($this->secretsManager);
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_name_exceeds_max_length(): void
    {
        // Arrange
        $data = [
            'org_id' => $this->org->id,
            'name' => str_repeat('a', 101), // 101 characters
            'status' => Status::ACTIVE->value,
            'host' => 'localhost',
            'port' => 5432,
            'dbms' => Dbms::POSTGRESQL->value,
            'username' => 'admin',
            'password' => 'secret',
        ];

        $request = new StoreAssetRequest($this->secretsManager);
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_nullable_description(): void
    {
        // Arrange
        $data = [
            'org_id' => $this->org->id,
            'name' => 'Test DB',
            'description' => null,
            'status' => Status::ACTIVE->value,
            'host' => 'localhost',
            'port' => 5432,
            'dbms' => Dbms::POSTGRESQL->value,
            'username' => 'admin',
            'password' => 'secret',
        ];

        $request = new StoreAssetRequest($this->secretsManager);
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_all_status_enum_values(): void
    {
        // Arrange
        $statuses = [Status::ACTIVE, Status::INACTIVE];

        foreach ($statuses as $status) {
            $data = [
                'org_id' => $this->org->id,
                'name' => 'Test DB',
                'status' => $status->value,
                'host' => 'localhost',
                'port' => 5432,
                'dbms' => Dbms::POSTGRESQL->value,
                'username' => 'admin',
                'password' => 'secret',
            ];

            $request = new StoreAssetRequest($this->secretsManager);
            $validator = Validator::make($data, $request->rules());

            // Assert
            $this->assertTrue($validator->passes(), "Failed for status: {$status->value}");
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_with_invalid_status(): void
    {
        // Arrange
        $data = [
            'org_id' => $this->org->id,
            'name' => 'Test DB',
            'status' => 'unknown',
            'host' => 'localhost',
            'port' => 5432,
            'dbms' => Dbms::POSTGRESQL->value,
            'username' => 'admin',
            'password' => 'secret',
        ];

        $request = new StoreAssetRequest($this->secretsManager);
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_port_within_valid_range(): void
    {
        // Arrange
        $validPorts = [0, 1, 80, 443, 5432, 3306, 65535];

        foreach ($validPorts as $port) {
            $data = [
                'org_id' => $this->org->id,
                'name' => 'Test DB',
                'status' => Status::ACTIVE->value,
                'host' => 'localhost',
                'port' => $port,
                'dbms' => Dbms::POSTGRESQL->value,
                'username' => 'admin',
                'password' => 'secret',
            ];

            $request = new StoreAssetRequest($this->secretsManager);
            $validator = Validator::make($data, $request->rules());

            // Assert
            $this->assertTrue($validator->passes(), "Failed for port: {$port}");
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_port_is_negative(): void
    {
        // Arrange
        $data = [
            'org_id' => $this->org->id,
            'name' => 'Test DB',
            'status' => Status::ACTIVE->value,
            'host' => 'localhost',
            'port' => -1,
            'dbms' => Dbms::POSTGRESQL->value,
            'username' => 'admin',
            'password' => 'secret',
        ];

        $request = new StoreAssetRequest($this->secretsManager);
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('port', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_port_exceeds_max(): void
    {
        // Arrange
        $data = [
            'org_id' => $this->org->id,
            'name' => 'Test DB',
            'status' => Status::ACTIVE->value,
            'host' => 'localhost',
            'port' => 65536,
            'dbms' => Dbms::POSTGRESQL->value,
            'username' => 'admin',
            'password' => 'secret',
        ];

        $request = new StoreAssetRequest($this->secretsManager);
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('port', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_all_dbms_enum_values(): void
    {
        // Arrange
        $dbmsList = [
            Dbms::MYSQL,
            Dbms::POSTGRESQL,
            Dbms::SQLSERVER,
            Dbms::MARIADB,
        ];

        foreach ($dbmsList as $dbms) {
            $data = [
                'org_id' => $this->org->id,
                'name' => 'Test DB',
                'status' => Status::ACTIVE->value,
                'host' => 'localhost',
                'port' => 5432,
                'dbms' => $dbms->value,
                'username' => 'admin',
                'password' => 'secret',
            ];

            $request = new StoreAssetRequest($this->secretsManager);
            $validator = Validator::make($data, $request->rules());

            // Assert
            $this->assertTrue($validator->passes(), "Failed for DBMS: {$dbms->value}");
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_with_invalid_dbms(): void
    {
        // Arrange
        $data = [
            'org_id' => $this->org->id,
            'name' => 'Test DB',
            'status' => Status::ACTIVE->value,
            'host' => 'localhost',
            'port' => 5432,
            'dbms' => 'invalid_dbms',
            'username' => 'admin',
            'password' => 'secret',
        ];

        $request = new StoreAssetRequest($this->secretsManager);
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('dbms', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_username_missing(): void
    {
        // Arrange
        $data = [
            'org_id' => $this->org->id,
            'name' => 'Test DB',
            'status' => Status::ACTIVE->value,
            'host' => 'localhost',
            'port' => 5432,
            'dbms' => Dbms::POSTGRESQL->value,
            'password' => 'secret',
        ];

        $request = new StoreAssetRequest($this->secretsManager);
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('username', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_password_missing(): void
    {
        // Arrange
        $data = [
            'org_id' => $this->org->id,
            'name' => 'Test DB',
            'status' => Status::ACTIVE->value,
            'host' => 'localhost',
            'port' => 5432,
            'dbms' => Dbms::POSTGRESQL->value,
            'username' => 'admin',
        ];

        $request = new StoreAssetRequest($this->secretsManager);
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_after_validation_hook(): void
    {
        // Arrange
        $request = new StoreAssetRequest($this->secretsManager);

        // Act
        $after = $request->after();

        // Assert
        $this->assertIsArray($after);
        $this->assertCount(1, $after);
        $this->assertIsCallable($after[0]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_constructs_with_secrets_manager_dependency(): void
    {
        // Arrange & Act
        $request = new StoreAssetRequest($this->secretsManager);

        // Assert
        $this->assertInstanceOf(StoreAssetRequest::class, $request);

        // Use reflection to verify dependency injection
        $reflection = new \ReflectionClass($request);
        $property = $reflection->getProperty('secretManager');
        $property->setAccessible(true);
        $injectedManager = $property->getValue($request);

        $this->assertSame($this->secretsManager, $injectedManager);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_extends_form_request(): void
    {
        // Arrange
        $request = new StoreAssetRequest($this->secretsManager);

        // Assert
        $this->assertInstanceOf(\Illuminate\Foundation\Http\FormRequest::class, $request);
    }
}
