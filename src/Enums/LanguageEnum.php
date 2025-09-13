<?php

namespace Sediqzada\InertiaBlueprint\Enums;

enum LanguageEnum: string
{
    case TS = 'ts';

    public function extension(): string
    {
        return 'tsx';
    }
}
