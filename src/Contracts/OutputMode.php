<?php
namespace MB\Support\HtmlCleaner\Contracts;

enum OutputMode: string
{
    case FRAGMENT = 'fragment';
    case DOCUMENT = 'document';
}