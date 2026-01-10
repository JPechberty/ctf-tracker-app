<?php

namespace App\Tests\Entity;

use App\Entity\Challenge;
use App\Entity\Flag;
use PHPUnit\Framework\TestCase;

class FlagTest extends TestCase
{
    public function testDefaultPointsIsZero(): void
    {
        $flag = new Flag();

        $this->assertEquals(0, $flag->getPoints());
    }

    public function testSettersAndGetters(): void
    {
        $flag = new Flag();
        $challenge = new Challenge();
        $challenge->setName('Test Challenge');
        $challenge->setStartDate(new \DateTimeImmutable());
        $challenge->setEndDate(new \DateTimeImmutable('+1 day'));

        $flag->setName('Flag 1');
        $flag->setValue('FLAG{test123}');
        $flag->setPoints(100);
        $flag->setChallenge($challenge);

        $this->assertEquals('Flag 1', $flag->getName());
        $this->assertEquals('FLAG{test123}', $flag->getValue());
        $this->assertEquals(100, $flag->getPoints());
        $this->assertSame($challenge, $flag->getChallenge());
    }

    public function testChallengeRelationship(): void
    {
        $challenge = new Challenge();
        $challenge->setName('Test Challenge');
        $challenge->setStartDate(new \DateTimeImmutable());
        $challenge->setEndDate(new \DateTimeImmutable('+1 day'));

        $flag = new Flag();
        $flag->setName('Flag 1');
        $flag->setValue('FLAG{test}');

        $challenge->addFlag($flag);

        $this->assertCount(1, $challenge->getFlags());
        $this->assertSame($challenge, $flag->getChallenge());
    }

    public function testRemoveFlagFromChallenge(): void
    {
        $challenge = new Challenge();
        $challenge->setName('Test Challenge');
        $challenge->setStartDate(new \DateTimeImmutable());
        $challenge->setEndDate(new \DateTimeImmutable('+1 day'));

        $flag = new Flag();
        $flag->setName('Flag 1');
        $flag->setValue('FLAG{test}');

        $challenge->addFlag($flag);
        $this->assertCount(1, $challenge->getFlags());

        $challenge->removeFlag($flag);
        $this->assertCount(0, $challenge->getFlags());
    }
}
