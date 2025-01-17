<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Message\SendMessage;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class MessageControllerTest extends WebTestCase
{
    use InteractsWithMessenger;

    protected AbstractDatabaseTool $databaseTool;
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
        $this->databaseTool->loadFixtures([AppFixtures::class]);
    }

    public function testList(): void
    {
        $this->client->request('GET', '/messages');

        $this->assertResponseIsSuccessful();

        $response = $this->client->getResponse();
        /** @var string $encodedContent */
        $encodedContent = $response->getContent();

        /** @var array<string, array<int, array{uuid: string, text: string, status: string}>> $decodedResponse */
        $decodedResponse = json_decode($encodedContent, true);

        $this->assertArrayHasKey('messages', $decodedResponse);

        $messages = $decodedResponse['messages'];

        $this->assertCount(AppFixtures::MESSAGES_COUNT, $messages);

        foreach ($messages as $message) {
            $this->assertArrayHasKey('uuid', $message);
            $this->assertArrayHasKey('text', $message);
            $this->assertArrayHasKey('status', $message);
        }
    }

    public function testListWithValidReadStatus(): void
    {
        $this->client->request('GET', '/messages', ['status' => 'read']);

        $this->assertResponseIsSuccessful();

        $response = $this->client->getResponse();

        /** @var string $encodedContent */
        $encodedContent = $response->getContent();

        /** @var array<string, array<int, array{uuid: string, text: string, status: string}>> $decodedResponse */
        $decodedResponse = json_decode($encodedContent, true);

        $this->assertArrayHasKey('messages', $decodedResponse);

        $messages = $decodedResponse['messages'];

        foreach ($messages as $message) {
            $this->assertEquals('read', $message['status']);
        }
    }

    public function testListWithValidSentStatus(): void
    {
        $this->client->request('GET', '/messages', ['status' => 'sent']);

        $this->assertResponseIsSuccessful();

        $response = $this->client->getResponse();

        /** @var string $encodedContent */
        $encodedContent = $response->getContent();

        /** @var array<string, array<int, array{uuid: string, text: string, status: string}>> $decodedResponse */
        $decodedResponse = json_decode($encodedContent, true);

        $this->assertArrayHasKey('messages', $decodedResponse);

        $messages = $decodedResponse['messages'];

        foreach ($messages as $message) {
            $this->assertEquals('sent', $message['status']);
        }
    }

    public function testListWithInvalidStatus(): void
    {
        $this->client->request('GET', '/messages', ['status' => 'invalid-status']);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testThatItSendsAMessage(): void
    {
        /** @var string $content */
        $content = json_encode(['text' => 'Hello, World!']);

        $this->client->request('POST', '/messages/send', content: $content);

        $this->assertResponseIsSuccessful();
        // This is using https://packagist.org/packages/zenstruck/messenger-test
        $this->transport('sync')
            ->queue()
            ->assertContains(SendMessage::class, 1);
    }

    protected function tearDown(): void
    {
        $this->databaseTool->loadFixtures([]);
        unset($this->databaseTool);
        parent::tearDown();
    }
}
