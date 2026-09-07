<?php

/**
 * (c) Packagist Conductors GmbH <contact@packagist.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrivatePackagist\ApiClient\HttpClient;

use PrivatePackagist\ApiClient\Exception\InvalidArgumentException;

/**
 * Builds a request path out of a literal template and untrusted arguments. A raw "#" or "?" in an
 * argument ends the path during URI parsing and drops what follows, turning
 * removePackage($customer, '#') into a DELETE on the whole collection. Arguments are encoded whole,
 * so a package name's "/" arrives as %2F.
 *
 * @internal
 */
final class RequestPath
{
    /**
     * @param string $template
     * @param string|int ...$arguments
     * @return string
     */
    public static function build($template, ...$arguments)
    {
        foreach ($arguments as $index => $argument) {
            $arguments[$index] = self::encodeArgument($argument);
        }

        return vsprintf($template, $arguments);
    }

    /**
     * @param string|int $argument
     * @return string
     */
    private static function encodeArgument($argument)
    {
        /** @var mixed $argument untyped at runtime; set to mixed, prevent PHPStan complaining about guard clauses */
        if (!is_string($argument) && !is_int($argument)) {
            throw new InvalidArgumentException(sprintf(
                'Path arguments must be a string or an integer, %s given.',
                is_object($argument) ? get_class($argument) : gettype($argument)
            ));
        }

        $argument = (string) $argument;

        if (in_array($argument, ['', '.' , '..'], true)) {
            throw new InvalidArgumentException("Path arguments must not be empty, '.', or '..'.");
        }

        return rawurlencode($argument);
    }
}
