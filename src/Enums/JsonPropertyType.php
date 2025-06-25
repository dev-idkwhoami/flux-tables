<?php

namespace Idkwhoami\FluxTables\Enums;

enum JsonPropertyType: string
{
    case Text = 'text';
    case Integer = 'integer';
    case BigInteger = 'bigint';
    case Float = 'double precision';
    case Numeric = 'numeric';
    case Boolean = 'boolean';
    case Timestamp = 'timestamp';
    case Date = 'date';
    case Time = 'time';
    case Interval = 'interval';
    case Uuid = 'uuid';
    case Inet = 'inet';
    case Cidr = 'cidr';
    case Bytea = 'bytea';
}
