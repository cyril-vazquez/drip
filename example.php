<?php
/*
 * Copyright (C) 2015 Maarch
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
require_once 'drip.php';
require_once 'abstractSequence.php';
require_once 'abstractFragment.php';
require_once 'leftFragment.php';
require_once 'rightFragment.php';
require_once 'subSequence.php';
require_once 'byteSequence.php';
require_once 'internalSignature.php';
require_once 'format.php';

$drip = new drip(__DIR__ . DIRECTORY_SEPARATOR . 'signatureFiles' . DIRECTORY_SEPARATOR . 'DROID_SignatureFile_V78.xml');

$fileResource = $drip->file(__FILE__);

$streamResource = $drip->buffer(file_get_contents('http://www.google.fr'));
$drip->loadSignaturefile(__DIR__ . DIRECTORY_SEPARATOR . 'signatureFiles' . DIRECTORY_SEPARATOR . 'DROID_SignatureFile_V78.xml');
$streamResource2 = $drip->buffer(file_get_contents('http://www.google.fr'));
echo "<pre>";
print_r($fileResource);
print_r($streamResource);

print_r($streamResource2);
echo "</pre>";
