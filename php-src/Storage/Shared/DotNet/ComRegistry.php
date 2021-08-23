<?php

namespace kalanis\kw_mapper\Storage\Database\Shared\DotNet;


use COM;


/**
 * Class ComRegistry
 * @package kalanis\kw_mapper\Storage\Database\Shared\DotNet
 * Dummy class for describe methods for accessing registry
 * @method string RegRead(string $key)
 * @method null RegWrite(string $key, mixed $content, string $type)
 * @method null RegDelete(string $key)
 * @link https://docs.microsoft.com/en-us/windows/win32/winprog64/accessing-an-alternate-registry-view
 * @link https://docs.microsoft.com/en-us/powershell/scripting/samples/working-with-registry-entries?view=powershell-7.1
 * @link https://ss64.com/vb/shell.html
 * @codeCoverageIgnore do not know for now how to check that
 */
class ComRegistry extends COM
{
    public function __construct($moduleName = null, $serverName = null, $codePage = null, $typeLib = null)
    {
        // @todo: nowhere to find if can be init with an empty values or not - look and change if need
        if (!is_null($typeLib)) {
            parent::__construct("WScript.Shell", $serverName, $codePage, $typeLib);
        } elseif (!is_null($codePage)) {
            parent::__construct("WScript.Shell", $serverName, $codePage);
        } elseif (!is_null($serverName)) {
            parent::__construct("WScript.Shell", $serverName);
        } else {
            parent::__construct("WScript.Shell");
        }
    }
}
