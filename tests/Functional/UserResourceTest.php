<?php

namespace App\Tests\Functional;

use App\Factory\DragonTreasureFactory;
use App\Factory\UserFactory;
use Zenstruck\Browser\Json;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserResourceTest extends ApiTestCase
{
    use ResetDatabase;

    public function testPostToCreateUser(): void
    {
        $this->browser()
            ->post( '/api/users', [
                'json' => [
                    'email' => 'test@test.com',
                    'password' => 'password',
                    'username' => 'test',
                ]
            ])
            ->assertStatus(201)
            ->use(function(Json $json) {
                $json->assertMissing('id');
                $json->assertMissing('password');
            })
            ->post('/login', [
                'email' => 'test@test.com',
                'password' => 'password',
            ])
            ->assertSuccessful()
            ;
    }

    public function testPatchToUpdateUser(): void
    {
        $user = UserFactory::createOne();

        $this->apiPatch()
            ->actingAs($user)
            ->patch('/api/users/' . $user->getId(), [
                'json' => [
                    'username' => 'changed',
                    'id' => 77
                ]
            ])
            ->assertStatus(200)
        ;
    }

    public function testTreasuresCannotBeStolen(): void
    {
        $user = UserFactory::createOne();
        $otherUser = UserFactory::createOne();
        $dragonTreasure = DragonTreasureFactory::createOne(
            ['owner' => $otherUser],
        );

        $this->apiPatch()
            ->actingAs($user)
            ->patch('/api/users/' . $user->getId(), [
                'json' => [
                    'username' => 'changed',
                    'dragonTreasures' => [
                        '/api/treasures/' . $dragonTreasure->getId(),
                    ]
                ],
                'headers' => ['Content-Type' => 'application/merge-patch+json'],
            ])
            ->assertStatus(422)
        ;
    }

    public function testUnpublishedTreasuresNotReturned(): void
    {
        $user = UserFactory::createOne();
        DragonTreasureFactory::createOne([
            'isPublished' => false,
            'owner' => $user,
        ]);

        $this->browser()
            ->actingAs(UserFactory::createOne())
            ->get('/api/users/' . $user->getId())
            ->assertJsonMatches('length("dragonTreasures")', 0);
    }
}