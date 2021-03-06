<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Extractor\Visitor\Php\Knp\Menu;

use PhpParser\Node;
use PhpParser\NodeVisitor;

/**
 * This class extracts knp menu item labels:
 *     - $menu->addChild('foo')
 *     - $menu['foo']->setLabel('bar').
 */
final class ItemLabel extends AbstractKnpMenuVisitor implements NodeVisitor
{
    public function enterNode(Node $node): ?Node
    {
        if (!$this->isKnpMenuBuildingMethod($node)) {
            return null;
        }

        parent::enterNode($node);

        if (!$node instanceof Node\Expr\MethodCall) {
            return null;
        }

        if (!\is_string($node->name) && !$node->name instanceof Node\Identifier) {
            return null;
        }

        $methodName = (string) $node->name;
        if (!\in_array($methodName, ['addChild', 'setLabel'], true)) {
            return null;
        }

        if (null !== $label = $this->getStringArgument($node, 0)) {
            $line = $node->getAttribute('startLine');
            if (null !== $location = $this->getLocation($label, $line, $node)) {
                $this->lateCollect($location);
            }
        }

        return null;
    }
}
