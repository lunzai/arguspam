<?php

namespace Tests\Unit\Services;

use App\Models\Asset;
use App\Models\Org;
use App\Models\Request;
use App\Models\Session;
use App\Models\User;
use App\Services\OpenAi\OpenAiService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Mockery;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Responses\CreateResponse;
use Tests\TestCase;

class OpenAiServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OpenAiService $service;

    protected Org $org;

    protected User $user;

    protected Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up test config
        Config::set('pam.openai', [
            'model' => 'gpt-4o-mini',
            'temperature' => 0.7,
            'max_output_tokens' => 16384,
            'top_p' => 0.95,
            'store' => false,
        ]);

        Config::set('pam.access_request.duration', [
            'min' => 1,
            'max' => 8,
            'recommended_min' => 2,
            'recommended_max' => 4,
            'low_threshold' => 240,
            'medium_threshold' => 1440,
            'high_threshold' => 10080,
        ]);

        $this->service = new OpenAiService;

        // Create test data
        $this->org = Org::factory()->create();
        $this->user = User::factory()->create();
        $this->org->users()->attach($this->user->id);
        $this->asset = Asset::factory()->create(['org_id' => $this->org->id]);
    }

    /**
     * Helper method to create CreateResponse instances since the class is final
     */
    protected function createMockResponse(array $overrides = []): CreateResponse
    {
        $defaults = [
            'id' => 'resp_123',
            'created_at' => 1234567890,
            'error' => null,
            'model' => 'gpt-4o-mini',
            'output_text' => '{"recommendation": "approve", "risk_level": "low"}',
            'reasoning' => ['effort' => 'low', 'generate_summary' => 'no'],
            'store' => false,
            'temperature' => 0.7,
            'usage' => null,
            'object' => 'response',
            'status' => 'completed',
            'instructions' => '',
            'max_output_tokens' => 16384,
            'output' => [],
            'parallel_tool_calls' => false,
            'previous_response_id' => null,
            'prompt' => null,
            'tool_choice' => 'auto',
            'tools' => [],
            'top_p' => 0.95,
            'truncation' => null,
            'user' => null,
            'verbosity' => null,
            'metadata' => null,
        ];

        return CreateResponse::from(
            array_merge($defaults, $overrides),
            \OpenAI\Responses\Meta\MetaInformation::from([])
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_constructs_with_config(): void
    {
        // Arrange & Act
        $service = new OpenAiService;

        // Assert
        $this->assertInstanceOf(OpenAiService::class, $service);

        // Verify config is loaded
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('config');
        $property->setAccessible(true);
        $config = $property->getValue($service);

        $this->assertIsArray($config);
        $this->assertEquals('gpt-4o-mini', $config['model']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_empty_array_for_review_session(): void
    {
        // Arrange
        $request = Request::factory()->create([
            'org_id' => $this->org->id,
            'requester_id' => $this->user->id,
            'asset_id' => $this->asset->id,
        ]);

        $session = Session::factory()->create([
            'org_id' => $this->org->id,
            'request_id' => $request->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
        ]);

        View::shouldReceive('make')
            ->twice() // system and user prompts
            ->andReturnSelf();
        View::shouldReceive('with')->andReturnSelf();
        View::shouldReceive('render')->andReturn('mocked prompt');

        // Act
        $result = $this->service->auditSession($session);

        // Assert
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_empty_array_for_audit_session(): void
    {
        // Arrange
        $request = Request::factory()->create([
            'org_id' => $this->org->id,
            'requester_id' => $this->user->id,
            'asset_id' => $this->asset->id,
        ]);

        $session = Session::factory()->create([
            'org_id' => $this->org->id,
            'request_id' => $request->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
        ]);

        // Act
        $result = $this->service->auditSession($session);

        // Assert
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // #[\PHPUnit\Framework\Attributes\Test]
    // public function it_evaluates_access_request_successfully(): void
    // {
    //     // Arrange
    //     $request = Request::factory()->create([
    //         'org_id' => $this->org->id,
    //         'requester_id' => $this->user->id,
    //         'asset_id' => $this->asset->id,
    //     ]);

    //     View::shouldReceive('make')->times(2)->andReturnSelf();
    //     View::shouldReceive('with')->andReturnSelf();
    //     View::shouldReceive('render')->andReturn('mocked prompt');

    //     File::shouldReceive('get')
    //         ->once()
    //         ->with(Mockery::type('string'))
    //         ->andReturn('{"type": "object", "properties": {}}');

    //     // Mock OpenAI response - CreateResponse is final, so we use the helper method
    //     $mockResponse = $this->createMockResponse([
    //         'usage' => [
    //             'input_tokens' => 100,
    //             'output_tokens' => 50,
    //             'total_tokens' => 150,
    //             'input_tokens_details' => [
    //                 'reasoning_tokens' => 20,
    //                 'cached_tokens' => 0,
    //             ],
    //             'output_tokens_details' => [
    //                 'reasoning_tokens' => 10,
    //             ],
    //         ]
    //     ]);

    //     // Note: CreateResponse is final, so we can't mock its methods
    //     // The toArray method will return the actual data

    //     $mockResponses = Mockery::mock();
    //     $mockResponses->shouldReceive('create')
    //         ->once()
    //         ->with(Mockery::type('array'))
    //         ->andReturn($mockResponse);

    //     OpenAI::shouldReceive('responses')
    //         ->once()
    //         ->andReturn($mockResponses);

    //     // Act
    //     $result = $this->service->evaluateAccessRequest($request);

    //     // Assert
    //     $this->assertIsArray($result);
    //     $this->assertArrayHasKey('id', $result);
    //     $this->assertArrayHasKey('output', $result);
    //     $this->assertArrayHasKey('usage', $result);
    //     $this->assertEquals('resp_123', $result['id']);
    //     $this->assertEquals('approve', $result['output']['recommendation']);
    // }

    // #[\PHPUnit\Framework\Attributes\Test]
    // public function it_logs_error_and_throws_exception_on_evaluation_failure(): void
    // {
    //     // Arrange
    //     $request = Request::factory()->create([
    //         'org_id' => $this->org->id,
    //         'requester_id' => $this->user->id,
    //         'asset_id' => $this->asset->id,
    //     ]);

    //     View::shouldReceive('make')->times(2)->andReturnSelf();
    //     View::shouldReceive('with')->andReturnSelf();
    //     View::shouldReceive('render')->andReturn('mocked prompt');

    //     File::shouldReceive('get')->once()->andReturn('{}');

    //     $mockResponses = Mockery::mock();
    //     $mockResponses->shouldReceive('create')
    //         ->once()
    //         ->andThrow(new Exception('API error'));

    //     OpenAI::shouldReceive('responses')->once()->andReturn($mockResponses);

    //     Log::shouldReceive('error')->once()->with(
    //         'Access request evaluation failed',
    //         Mockery::type('array')
    //     );

    //     // Assert
    //     $this->expectException(Exception::class);
    //     $this->expectExceptionMessage('API error');

    //     // Act
    //     $this->service->evaluateAccessRequest($request);
    // }

    // #[\PHPUnit\Framework\Attributes\Test]
    // public function it_prepares_response_with_json_output(): void
    // {
    //     // Arrange
    //     $mockResponse = $this->createMockResponse([
    //         'id' => 'resp_456',
    //         'created_at' => 9876543210,
    //         'model' => 'gpt-4o',
    //         'output_text' => '{"status": "success"}',
    //         'reasoning' => ['effort' => 'medium', 'generate_summary' => 'yes'],
    //         'store' => true,
    //         'temperature' => 0.5,
    //     ]);

    //     $mockUsage = Mockery::mock();
    //     $mockUsage->inputTokens = 200;
    //     $mockUsage->outputTokens = 100;
    //     $mockUsage->totalTokens = 300;

    //     $mockInputTokensDetails = Mockery::mock();
    //     $mockInputTokensDetails->cachedTokens = 50;
    //     $mockUsage->inputTokensDetails = $mockInputTokensDetails;

    //     $mockOutputTokensDetails = Mockery::mock();
    //     $mockOutputTokensDetails->reasoningTokens = 20;
    //     $mockUsage->outputTokensDetails = $mockOutputTokensDetails;

    //     // Note: usage is readonly and must be set during creation
    //     // Note: CreateResponse is final, so we can't mock its methods

    //     // Act
    //     $reflection = new \ReflectionMethod($this->service, 'prepareResponse');
    //     $reflection->setAccessible(true);
    //     $result = $reflection->invoke($this->service, $mockResponse, true);

    //     // Assert
    //     $this->assertIsArray($result);
    //     $this->assertEquals('resp_456', $result['id']);
    //     $this->assertEquals('gpt-4o', $result['model']);
    //     $this->assertIsArray($result['output']);
    //     $this->assertEquals('success', $result['output']['status']);
    //     $this->assertEquals(200, $result['usage']['input_tokens']);
    //     $this->assertEquals(50, $result['usage']['cache_tokens']);
    //     $this->assertEquals(20, $result['usage']['reasoning_tokens']);
    // }

    // #[\PHPUnit\Framework\Attributes\Test]
    // public function it_prepares_response_with_text_output(): void
    // {
    //     // Arrange
    //     $mockResponse = $this->createMockResponse([
    //         'id' => 'resp_789',
    //         'created_at' => 1111111111,
    //         'model' => 'gpt-4o-mini',
    //         'output_text' => 'Plain text output',
    //         'reasoning' => ['effort' => 'low', 'generate_summary' => 'no'],
    //         'store' => false,
    //         'temperature' => 0.8,
    //     ]);

    //     $mockUsage = Mockery::mock();
    //     $mockUsage->inputTokens = 50;
    //     $mockUsage->outputTokens = 25;
    //     $mockUsage->totalTokens = 75;

    //     $mockInputTokensDetails = Mockery::mock();
    //     $mockInputTokensDetails->cachedTokens = 0;
    //     $mockUsage->inputTokensDetails = $mockInputTokensDetails;

    //     $mockOutputTokensDetails = Mockery::mock();
    //     $mockOutputTokensDetails->reasoningTokens = 0;
    //     $mockUsage->outputTokensDetails = $mockOutputTokensDetails;

    //     // Note: usage is readonly and must be set during creation
    //     // Note: CreateResponse is final, so we can't mock its methods

    //     // Act
    //     $reflection = new \ReflectionMethod($this->service, 'prepareResponse');
    //     $reflection->setAccessible(true);
    //     $result = $reflection->invoke($this->service, $mockResponse, false);

    //     // Assert
    //     $this->assertIsString($result['output']);
    //     $this->assertEquals('Plain text output', $result['output']);
    // }

    // #[\PHPUnit\Framework\Attributes\Test]
    // public function it_gets_response_from_openai_with_correct_parameters(): void
    // {
    //     // Arrange
    //     $systemPrompt = 'You are a helpful assistant';
    //     $userPrompt = 'Evaluate this request';
    //     $format = ['type' => 'object'];

    //     $mockResponse = $this->createMockResponse();

    //     $mockResponses = Mockery::mock();
    //     $mockResponses->shouldReceive('create')
    //         ->once()
    //         ->with(Mockery::on(function ($params) use ($systemPrompt, $userPrompt, $format) {
    //             return $params['model'] === 'gpt-4o-mini'
    //                 && $params['input'][0]['role'] === 'system'
    //                 && $params['input'][0]['content'] === $systemPrompt
    //                 && $params['input'][1]['role'] === 'user'
    //                 && $params['input'][1]['content'] === $userPrompt
    //                 && $params['text'] === $format
    //                 && $params['temperature'] === 0.7;
    //         }))
    //         ->andReturn($mockResponse);

    //     OpenAI::shouldReceive('responses')->once()->andReturn($mockResponses);

    //     // Act
    //     $reflection = new \ReflectionMethod($this->service, 'getResponse');
    //     $reflection->setAccessible(true);
    //     $result = $reflection->invoke($this->service, $systemPrompt, $userPrompt, $format);

    //     // Assert
    //     $this->assertInstanceOf(CreateResponse::class, $result);
    // }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_gets_format_from_json_file(): void
    {
        // Arrange
        $path = 'prompts/test-format.json';
        $jsonContent = '{"type": "object", "properties": {"name": {"type": "string"}}}';
        $expectedFormat = ['type' => 'object', 'properties' => ['name' => ['type' => 'string']]];

        File::shouldReceive('get')
            ->once()
            ->with(resource_path($path))
            ->andReturn($jsonContent);

        // Act
        $reflection = new \ReflectionMethod($this->service, 'getFormat');
        $reflection->setAccessible(true);
        $result = $reflection->invoke($this->service, $path);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($expectedFormat, $result);
    }

    // #[\PHPUnit\Framework\Attributes\Test]
    // public function it_merges_duration_config_in_evaluate_access_request(): void
    // {
    //     // Arrange
    //     $request = Request::factory()->create([
    //         'org_id' => $this->org->id,
    //         'requester_id' => $this->user->id,
    //         'asset_id' => $this->asset->id,
    //     ]);

    //     View::shouldReceive('make')->times(2)->andReturnSelf();
    //     View::shouldReceive('with')
    //         ->twice()
    //         ->with(Mockery::on(function ($data) {
    //             if (isset($data['config'])) {
    //                 return isset($data['config']['min'])
    //                     && isset($data['config']['max'])
    //                     && isset($data['config']['model']);
    //             }
    //             return true;
    //         }))
    //         ->andReturnSelf();
    //     View::shouldReceive('render')->andReturn('prompt');

    //     File::shouldReceive('get')->once()->andReturn('{}');

    //     $mockResponse = $this->createMockResponse([
    //         'id' => 'test',
    //         'created_at' => 123,
    //         'model' => 'gpt-4o-mini',
    //         'output_text' => '{}',
    //         'reasoning' => ['effort' => 'low', 'generate_summary' => 'no'],
    //         'store' => false,
    //         'temperature' => 0.7,
    //     ]);

    //     $mockUsage = Mockery::mock();
    //     $mockUsage->inputTokens = 10;
    //     $mockUsage->outputTokens = 10;
    //     $mockUsage->totalTokens = 20;
    //     $mockUsage->inputTokensDetails = Mockery::mock(['cachedTokens' => 0]);
    //     $mockUsage->outputTokensDetails = Mockery::mock(['reasoningTokens' => 0]);
    //     // Note: usage is readonly and must be set during creation
    //     // Note: CreateResponse is final, so we can't mock its methods

    //     $mockResponses = Mockery::mock();
    //     $mockResponses->shouldReceive('create')->once()->andReturn($mockResponse);
    //     OpenAI::shouldReceive('responses')->once()->andReturn($mockResponses);

    //     // Act
    //     $result = $this->service->evaluateAccessRequest($request);

    //     // Assert
    //     $this->assertIsArray($result);
    // }
}
