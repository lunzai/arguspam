<?php

namespace Tests\Unit\Services;

use App\Services\SlackBlockKitBuilder;
use Tests\TestCase;

class SlackBlockKitBuilderTest extends TestCase
{
    private SlackBlockKitBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new SlackBlockKitBuilder;
    }

    public function test_header_message_without_accessories(): void
    {
        $block = $this->builder->headerMessage('Hello World');

        $this->assertEquals('section', $block['type']);
        $this->assertEquals('mrkdwn', $block['text']['type']);
        $this->assertEquals('Hello World', $block['text']['text']);
        $this->assertArrayNotHasKey('accessory', $block);
    }

    public function test_header_message_with_accessories(): void
    {
        $accessory = ['type' => 'button', 'text' => 'Click'];

        $block = $this->builder->headerMessage('Hello World', $accessory);

        $this->assertEquals('section', $block['type']);
        $this->assertEquals($accessory, $block['accessory']);
    }

    public function test_button_returns_correct_structure(): void
    {
        $block = $this->builder->button('Click Me', 'https://example.com');

        $this->assertEquals('button', $block['type']);
        $this->assertEquals('plain_text', $block['text']['type']);
        $this->assertEquals('Click Me', $block['text']['text']);
        $this->assertTrue($block['text']['emoji']);
        $this->assertEquals('https://example.com', $block['url']);
        $this->assertEquals('button-action', $block['action_id']);
    }

    public function test_divider_returns_correct_structure(): void
    {
        $block = $this->builder->divider();

        $this->assertEquals(['type' => 'divider'], $block);
    }

    public function test_section_message_returns_correct_structure(): void
    {
        $block = $this->builder->sectionMessage('Section content');

        $this->assertEquals('section', $block['type']);
        $this->assertEquals('mrkdwn', $block['text']['type']);
        $this->assertEquals('Section content', $block['text']['text']);
    }

    public function test_context_message_returns_correct_structure(): void
    {
        $block = $this->builder->contextMessage('Context text');

        $this->assertEquals('context', $block['type']);
        $this->assertIsArray($block['elements']);
        $this->assertEquals('mrkdwn', $block['elements'][0]['type']);
        $this->assertEquals('Context text', $block['elements'][0]['text']);
    }

    public function test_build_session_notification_blocks_returns_array(): void
    {
        $blocks = SlackBlockKitBuilder::buildSessionNotificationBlocks(null, 'Session started');

        $this->assertIsArray($blocks);
        $this->assertNotEmpty($blocks);
        $this->assertEquals('section', $blocks[0]['type']);
        $this->assertEquals('Session started', $blocks[0]['text']['text']);
    }

    public function test_build_session_notification_blocks_uses_default_message(): void
    {
        $blocks = SlackBlockKitBuilder::buildSessionNotificationBlocks(null);

        $this->assertEquals('Session notification', $blocks[0]['text']['text']);
    }
}
