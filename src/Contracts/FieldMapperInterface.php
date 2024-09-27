<?php

namespace Asif\AutoFactory\Contracts;

interface FieldMapperInterface
{
    public function generateFactoryFields($table, $columns);
}
