<?php

declare(strict_types=1);

namespace CamelMailer\Tests;

use CamelMailer\ApiObject;
use LogicException;

final class ApiObjectTest extends TestCase
{
    public function test_attributes_are_readable_as_properties_and_array_offsets(): void
    {
        $object = ApiObject::from(['message_id' => 42, 'tag' => 'receipt']);

        $this->assertSame(42, $object->message_id);
        $this->assertSame(42, $object['message_id']);
        $this->assertSame('receipt', $object->tag);
        $this->assertTrue(isset($object->tag));
        $this->assertTrue(isset($object['tag']));
        $this->assertFalse(isset($object['missing']));
        $this->assertNull($object->missing);
    }

    public function test_it_converts_back_to_an_array_and_serializes_to_json(): void
    {
        $attributes = ['stats' => ['sent' => 10, 'bounced' => 1]];
        $object = ApiObject::from($attributes);

        $this->assertSame($attributes, $object->toArray());
        $this->assertSame(json_encode($attributes), json_encode($object));
    }

    public function test_it_is_immutable(): void
    {
        $object = ApiObject::from(['a' => 1]);

        $this->expectException(LogicException::class);
        $object['a'] = 2;
    }

    public function test_unset_is_rejected(): void
    {
        $object = ApiObject::from(['a' => 1]);

        $this->expectException(LogicException::class);
        unset($object['a']);
    }
}
