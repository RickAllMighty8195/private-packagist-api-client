<?php

/**
 * (c) Packagist Conductors GmbH <contact@packagist.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrivatePackagist\ApiClient\PHPStan;

use Http\Client\Common\HttpMethodsClient;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use PrivatePackagist\ApiClient\Api\AbstractApi;
use PrivatePackagist\ApiClient\HttpClient\RequestPath;

/**
 * An unencoded "#" or "?" in a path argument cuts the path short and moves the request to another
 * endpoint, so a request path has to be a literal or come out of a builder with a literal template.
 *
 * @implements Rule<Node\Expr>
 */
class RequestPathRule implements Rule
{
    /** @var string[] AbstractApi's request methods and the HttpMethodsClient verbs, all taking the path first */
    private static $requestMethods = ['get', 'getcollection', 'post', 'postfile', 'put', 'delete', 'patch', 'head', 'options', 'trace'];

    /** @var string[] php-http/client-common ^1.9 has no HttpMethodsClientInterface, hence both */
    private static $httpClientTypes = [HttpMethodsClient::class, 'Http\Client\Common\HttpMethodsClientInterface'];

    public function getNodeType(): string
    {
        return Node\Expr::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($node instanceof StaticCall) {
            return $this->isRequestPathBuild($node, $scope) ? $this->checkTemplate($node->args, 'RequestPath::build()') : [];
        }

        if (!$node instanceof MethodCall || !$node->name instanceof Identifier) {
            return [];
        }

        if ($this->isNamed($node, 'buildpath')) {
            return $this->isApiCall($node, $scope) ? $this->checkTemplate($node->args, '$this->buildPath()') : [];
        }

        if (!in_array($node->name->toLowerString(), self::$requestMethods, true)) {
            return [];
        }

        if (!$this->isApiCall($node, $scope) && !$this->isHttpClientRequest($node, $scope)) {
            return [];
        }

        if (!isset($node->args[0]) || !$node->args[0] instanceof Node\Arg) {
            return [];
        }

        $path = $node->args[0]->value;
        if ($path instanceof String_ || $this->isPathBuilder($path, $scope)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Path passed to %s() must be a literal string or built with $this->buildPath() or RequestPath::build(), so that its arguments are URL-encoded.',
                $node->name->toString()
            ))->build(),
        ];
    }

    /**
     * buildPath('/packages/' . $name . '/') would otherwise smuggle an unencoded name past the rule.
     *
     * @param array<Node\Arg|Node\VariadicPlaceholder> $arguments
     * @return list<\PHPStan\Rules\RuleError>
     */
    private function checkTemplate(array $arguments, string $builder): array
    {
        if (isset($arguments[0]) && $arguments[0] instanceof Node\Arg && $arguments[0]->value instanceof String_) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Template passed to %s must be a literal string, so that only its arguments are URL-encoded. Pass the dynamic parts as arguments.',
                $builder
            ))->build(),
        ];
    }

    /**
     * Accepted because the builder's own template is checked wherever the call itself is visited.
     */
    private function isPathBuilder(Node\Expr $path, Scope $scope): bool
    {
        if ($path instanceof MethodCall) {
            return $this->isNamed($path, 'buildpath') && $this->isApiCall($path, $scope);
        }

        return $path instanceof StaticCall && $this->isRequestPathBuild($path, $scope);
    }

    private function isApiCall(MethodCall $node, Scope $scope): bool
    {
        if (!$this->isOnThis($node)) {
            return false;
        }

        $class = $scope->getClassReflection();

        return $class !== null && ($class->getName() === AbstractApi::class || $class->isSubclassOf(AbstractApi::class));
    }

    private function isHttpClientRequest(MethodCall $node, Scope $scope): bool
    {
        if ($this->isAbstractApi($scope)) {
            return false;
        }

        $type = $scope->getType($node->var);
        foreach (self::$httpClientTypes as $httpClientType) {
            if ((new ObjectType($httpClientType))->isSuperTypeOf($type)->yes()) {
                return true;
            }
        }

        return false;
    }

    private function isRequestPathBuild(StaticCall $call, Scope $scope): bool
    {
        if (!$call->class instanceof Name || !$this->isNamed($call, 'build')) {
            return false;
        }

        return $scope->resolveName($call->class) === RequestPath::class && !$this->isAbstractApi($scope);
    }

    /**
     * AbstractApi forwards the path and the template its own methods were handed, so it is checked at
     * its call sites instead. The pagination link it follows is validated at runtime by
     * assertSameOriginAsPrivatePackagist().
     */
    private function isAbstractApi(Scope $scope): bool
    {
        $class = $scope->getClassReflection();

        return $class !== null && $class->getName() === AbstractApi::class;
    }

    private function isOnThis(MethodCall $call): bool
    {
        return $call->var instanceof Variable && $call->var->name === 'this';
    }

    /**
     * @param MethodCall|StaticCall $call
     */
    private function isNamed($call, string $lowercaseName): bool
    {
        return $call->name instanceof Identifier && $call->name->toLowerString() === $lowercaseName;
    }
}
