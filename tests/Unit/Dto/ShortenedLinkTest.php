<?php

declare(strict_types=1);

namespace Calisero\Sms\Tests\Unit\Dto;

use Calisero\Sms\Dto\ShortenedLink;
use PHPUnit\Framework\TestCase;

class ShortenedLinkTest extends TestCase
{
    public function testCanCreateFromArray(): void
    {
        $link = ShortenedLink::fromArray([
            'id' => '019adfbb-40a1-71ee-bcb5-8d551b8cfdae',
            'original_link' => 'https://example.com/orders/12345?utm_source=sms',
            'shortened_link' => 'https://calisero.ro/s/ghJKPV',
            'click_count' => 3,
            'last_click' => '2025-12-02T16:10:00.000000Z',
            'created_at' => '2025-12-02T15:43:02.000000Z',
        ]);

        $this->assertSame('019adfbb-40a1-71ee-bcb5-8d551b8cfdae', $link->getId());
        $this->assertSame('https://example.com/orders/12345?utm_source=sms', $link->getOriginalLink());
        $this->assertSame('https://calisero.ro/s/ghJKPV', $link->getShortenedLink());
        $this->assertSame(3, $link->getClickCount());
        $this->assertSame('2025-12-02T16:10:00.000000Z', $link->getLastClick());
        $this->assertSame('2025-12-02T15:43:02.000000Z', $link->getCreatedAt());
    }

    public function testLinkNeverClicked(): void
    {
        $link = ShortenedLink::fromArray([
            'id' => '019adfbb-40a1-71ee-bcb5-8d551b8cfdae',
            'original_link' => 'https://example.com',
            'shortened_link' => 'https://calisero.ro/s/ghJKPV',
            'click_count' => 0,
            'last_click' => null,
            'created_at' => '2025-12-02T15:43:02.000000Z',
        ]);

        $this->assertSame(0, $link->getClickCount());
        $this->assertNull($link->getLastClick());
    }
}
