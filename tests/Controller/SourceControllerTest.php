<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Tests\Controller;

use App\DataFixtures\SourceFixtures;
use App\Entity\Source;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\Tests\ControllerBaseCase;

class SourceControllerTest extends ControllerBaseCase {
    protected function fixtures() : array {
        return [
            UserFixtures::class,
            SourceFixtures::class,
        ];
    }

    public function testAnonIndex() : void {

        $crawler = $this->client->request('GET', '/source/');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());


        $this->assertSame(0, $crawler->selectLink('New')->count());
    }

    public function testUserIndex() : void {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/source/');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertSame(0, $crawler->selectLink('New')->count());
    }

    public function testAdminIndex() : void {
        $this->login('user.admin');
        $crawler = $this->client->request('GET', '/source/');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertSame(1, $crawler->selectLink('New')->count());
    }

    public function testAnonShow() : void {

        $crawler = $this->client->request('GET', '/source/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());


        $this->assertSame(0, $crawler->selectLink('Edit')->count());
        $this->assertSame(0, $crawler->selectLink('Delete')->count());
    }

    public function testUserShow() : void {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/source/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertSame(0, $crawler->selectLink('Edit')->count());
        $this->assertSame(0, $crawler->selectLink('Delete')->count());
    }

    public function testAdminShow() : void {
        $this->login('user.admin');
        $crawler = $this->client->request('GET', '/source/1');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertSame(1, $crawler->selectLink('Edit')->count());
        $this->assertSame(1, $crawler->selectLink('Delete')->count());
    }

    public function testAnonEdit() : void {

        $crawler = $this->client->request('GET', '/source/1/edit');
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());

        $this->assertTrue($this->client->getResponse()->isRedirect());
    }

    public function testUserEdit() : void {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/source/1/edit');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminEdit() : void {
        $this->login('user.admin');
        $formCrawler = $this->client->request('GET', '/source/1/edit');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $form = $formCrawler->selectButton('Update')->form([
            'source[name]' => 'Cheese.',
            'source[label]' => 'Cheese',
            'source[description]' => 'It is a cheese.',
            'source[date]' => 'April 1972',
        ]);

        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirect('/source/1'));
        $responseCrawler = $this->client->followRedirect();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertSame(1, $responseCrawler->filter('td:contains("It is a cheese.")')->count());
    }

    public function testAnonNew() : void {

        $crawler = $this->client->request('GET', '/source/new');
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());

        $this->assertTrue($this->client->getResponse()->isRedirect());
    }

    public function testUserNew() : void {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/source/new');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminNew() : void {
        $this->login('user.admin');
        $formCrawler = $this->client->request('GET', '/source/new');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $form = $formCrawler->selectButton('Create')->form([
            'source[name]' => 'Cheese.',
            'source[label]' => 'Cheese',
            'source[description]' => 'It is a cheese.',
            'source[date]' => 'April 1972',
        ]);

        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $responseCrawler = $this->client->followRedirect();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertSame(1, $responseCrawler->filter('td:contains("It is a cheese.")')->count());
    }

    public function testAnonDelete() : void {

        $crawler = $this->client->request('GET', '/source/1/delete');
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());

        $this->assertTrue($this->client->getResponse()->isRedirect());
    }

    public function testUserDelete() : void {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/source/1/delete');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminDelete() : void {
        $preCount = count($this->entityManager->getRepository(Source::class)->findAll());
        $this->login('user.admin');
        $crawler = $this->client->request('GET', '/source/1/delete');
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $responseCrawler = $this->client->followRedirect();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->entityManager->clear();
        $postCount = count($this->entityManager->getRepository(Source::class)->findAll());
        $this->assertSame($preCount - 1, $postCount);
    }
}
