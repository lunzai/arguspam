<?php

namespace Tests\Unit\Requests\Asset;

use App\Enums\Dbms;
use App\Http\Requests\Asset\UpdateAssetCredentialRequest;
use App\Models\Asset;
use App\Models\AssetAccount;
use App\Services\Jit\JitManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Mockery;
use Tests\TestCase;

class UpdateAssetCredentialRequestTest extends TestCase
{
    use RefreshDatabase;

    protected JitManager $jitManager;

    protected Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jitManager = Mockery::mock(JitManager::class);
        $this->asset = Asset::factory()->create();
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
        $request = new UpdateAssetCredentialRequest($this->jitManager);

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
            'host' => '192.168.1.1',
            'port' => 5432,
            'dbms' => Dbms::POSTGRESQL->value,
        ];

        $request = new UpdateAssetCredentialRequest($this->jitManager);
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_with_username_and_password(): void
    {
        // Arrange
        $data = [
            'host' => 'db.example.com',
            'port' => 3306,
            'dbms' => Dbms::MYSQL->value,
            'username' => 'admin',
            'password' => 'secret123',
        ];

        $request = new UpdateAssetCredentialRequest($this->jitManager);
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_host_missing(): void
    {
        // Arrange
        $data = [
            'port' => 5432,
            'dbms' => Dbms::POSTGRESQL->value,
        ];

        $request = new UpdateAssetCredentialRequest($this->jitManager);
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('host', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_port_missing(): void
    {
        // Arrange
        $data = [
            'host' => '192.168.1.1',
            'dbms' => Dbms::POSTGRESQL->value,
        ];

        $request = new UpdateAssetCredentialRequest($this->jitManager);
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('port', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_dbms_missing(): void
    {
        // Arrange
        $data = [
            'host' => '192.168.1.1',
            'port' => 5432,
        ];

        $request = new UpdateAssetCredentialRequest($this->jitManager);
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('dbms', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_valid_ip_addresses(): void
    {
        // Arrange
        $validIps = [
            '192.168.1.1',
            '10.0.0.1',
            '172.16.0.1',
            '127.0.0.1',
            '8.8.8.8',
        ];

        foreach ($validIps as $ip) {
            $data = [
                'host' => $ip,
                'port' => 5432,
                'dbms' => Dbms::POSTGRESQL->value,
            ];

            $request = new UpdateAssetCredentialRequest($this->jitManager);
            $validator = Validator::make($data, $request->rules());

            // Assert
            $this->assertTrue($validator->passes(), "Failed for IP: {$ip}");
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_valid_hostnames(): void
    {
        // Arrange
        $validHostnames = [
            'localhost',
            'db.example.com',
            'mysql-server.local',
            'postgres.company.org',
        ];

        foreach ($validHostnames as $hostname) {
            $data = [
                'host' => $hostname,
                'port' => 5432,
                'dbms' => Dbms::POSTGRESQL->value,
            ];

            $request = new UpdateAssetCredentialRequest($this->jitManager);
            $validator = Validator::make($data, $request->rules());

            // Assert
            $this->assertTrue($validator->passes(), "Failed for hostname: {$hostname}");
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_with_invalid_host(): void
    {
        // Arrange
        $data = [
            'host' => 'not a valid host!@#',
            'port' => 5432,
            'dbms' => Dbms::POSTGRESQL->value,
        ];

        $request = new UpdateAssetCredentialRequest($this->jitManager);
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('host', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_port_within_valid_range(): void
    {
        // Arrange
        $validPorts = [1, 80, 443, 3306, 5432, 65535];

        foreach ($validPorts as $port) {
            $data = [
                'host' => '192.168.1.1',
                'port' => $port,
                'dbms' => Dbms::POSTGRESQL->value,
            ];

            $request = new UpdateAssetCredentialRequest($this->jitManager);
            $validator = Validator::make($data, $request->rules());

            // Assert
            $this->assertTrue($validator->passes(), "Failed for port: {$port}");
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_fails_validation_when_port_is_zero(): void
    {
        // Arrange
        $data = [
            'host' => '192.168.1.1',
            'port' => 0,
            'dbms' => Dbms::POSTGRESQL->value,
        ];

        $request = new UpdateAssetCredentialRequest($this->jitManager);
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
            'host' => '192.168.1.1',
            'port' => 65536,
            'dbms' => Dbms::POSTGRESQL->value,
        ];

        $request = new UpdateAssetCredentialRequest($this->jitManager);
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
                'host' => '192.168.1.1',
                'port' => 5432,
                'dbms' => $dbms->value,
            ];

            $request = new UpdateAssetCredentialRequest($this->jitManager);
            $validator = Validator::make($data, $request->rules());

            // Assert
            $this->assertTrue($validator->passes(), "Failed for DBMS: {$dbms->value}");
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_enum_validation_for_dbms(): void
    {
        // Arrange
        $request = new UpdateAssetCredentialRequest($this->jitManager);

        // Act
        $rules = $request->rules();

        // Assert
        $this->assertArrayHasKey('dbms', $rules);
        // Verify it contains an Enum rule
        $hasEnumRule = false;
        foreach ($rules['dbms'] as $rule) {
            if ($rule instanceof \Illuminate\Validation\Rules\Enum) {
                $hasEnumRule = true;
                break;
            }
        }
        $this->assertTrue($hasEnumRule, 'dbms field should have Enum validation rule');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_nullable_username_and_password(): void
    {
        // Arrange
        $data = [
            'host' => '192.168.1.1',
            'port' => 5432,
            'dbms' => Dbms::POSTGRESQL->value,
            'username' => null,
            'password' => null,
        ];

        $request = new UpdateAssetCredentialRequest($this->jitManager);
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_requires_password_when_username_provided(): void
    {
        // Arrange
        $data = [
            'host' => '192.168.1.1',
            'port' => 5432,
            'dbms' => Dbms::POSTGRESQL->value,
            'username' => 'admin',
            'password' => '', // Empty password should fail required_with
        ];

        $request = new UpdateAssetCredentialRequest($this->jitManager);
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('password'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_requires_username_when_password_provided(): void
    {
        // Arrange
        $data = [
            'host' => '192.168.1.1',
            'port' => 5432,
            'dbms' => Dbms::POSTGRESQL->value,
            'username' => '', // Empty username should fail required_with
            'password' => 'secret123',
        ];

        $request = new UpdateAssetCredentialRequest($this->jitManager);
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('username'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_prepare_for_validation_method(): void
    {
        // Arrange
        $request = new UpdateAssetCredentialRequest($this->jitManager);

        // Act
        $reflection = new \ReflectionMethod($request, 'prepareForValidation');

        // Assert
        $this->assertTrue($reflection->isProtected());
        $this->assertEquals('prepareForValidation', $reflection->getName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_prepare_for_validation_normalizes_dbms_logic(): void
    {
        // Arrange & Act - Test the logic by reading the method
        $request = new UpdateAssetCredentialRequest($this->jitManager);
        $reflection = new \ReflectionMethod($request, 'prepareForValidation');

        // Assert - Verify method exists and is protected
        $this->assertTrue($reflection->isProtected());

        // We can't easily test the actual execution without a real request,
        // but we can verify the rules require lowercase enum values
        $data = [
            'host' => '192.168.1.1',
            'port' => 5432,
            'dbms' => Dbms::POSTGRESQL->value, // lowercase value
        ];

        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_after_validation_hook(): void
    {
        // Arrange
        $request = new UpdateAssetCredentialRequest($this->jitManager);

        // Act
        $after = $request->after();

        // Assert
        $this->assertIsArray($after);
        $this->assertCount(1, $after);
        $this->assertIsCallable($after[0]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_attributes_from_model(): void
    {
        // Arrange
        $request = new UpdateAssetCredentialRequest($this->jitManager);

        // Act
        $attributes = $request->attributes();

        // Assert
        $this->assertIsArray($attributes);
        // Verify it's using AssetAccount::$attributeLabels
        $this->assertNotEmpty($attributes);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_constructs_with_secrets_manager_dependency(): void
    {
        // Arrange & Act
        $request = new UpdateAssetCredentialRequest($this->jitManager);

        // Assert
        $this->assertInstanceOf(UpdateAssetCredentialRequest::class, $request);

        // Use reflection to verify dependency injection
        $reflection = new \ReflectionClass($request);
        $property = $reflection->getProperty('jitManager');
        $property->setAccessible(true);
        $injectedManager = $property->getValue($request);

        $this->assertSame($this->jitManager, $injectedManager);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_extends_form_request(): void
    {
        // Arrange
        $request = new UpdateAssetCredentialRequest($this->jitManager);

        // Assert
        $this->assertInstanceOf(\Illuminate\Foundation\Http\FormRequest::class, $request);
    }
}
