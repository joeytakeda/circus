<?php

namespace AppBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class to build some menus for navigation.
 */
class Builder implements ContainerAwareInterface {
    use ContainerAwareTrait;

    const CARET = ' ▾'; // U+25BE, black down-pointing small triangle.

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authChecker;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(FactoryInterface $factory, AuthorizationCheckerInterface $authChecker, TokenStorageInterface $tokenStorage) {
        $this->factory = $factory;
        $this->authChecker = $authChecker;
        $this->tokenStorage = $tokenStorage;
    }

    private function hasRole($role) {
        if ( ! $this->tokenStorage->getToken()) {
            return false;
        }

        return $this->authChecker->isGranted($role);
    }

    /**
     * Build a menu for blog posts.
     *
     * @param array $options
     *
     * @return ItemInterface
     */
    public function mainMenu(array $options) {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttributes(array(
            'class' => 'nav navbar-nav',
        ));

        $menu->addChild('home', array(
            'label' => 'Home',
            'route' => 'homepage',
        ));

        $browse = $menu->addChild('browse', array(
            'uri' => '#',
            'label' => 'Browse ' . self::CARET,
        ));
        $browse->setAttribute('dropdown', true);
        $browse->setLinkAttribute('class', 'dropdown-toggle');
        $browse->setLinkAttribute('data-toggle', 'dropdown');
        $browse->setChildrenAttribute('class', 'dropdown-menu');

        $browse->addChild('clippings', array(
            'label' => 'Clippings',
            'route' => 'clipping_index',
        ));

        if ($this->hasRole('ROLE_USER')) {
            $divider = $browse->addChild('divider', array(
                'label' => '',
            ));
            $divider->setAttributes(array(
                'role' => 'separator',
                'class' => 'divider',
            ));

            $browse->addChild('categories', array(
                'label' => 'Categories',
                'route' => 'category_index',
            ));
            $browse->addChild('sources', array(
                'label' => 'Sources',
                'route' => 'source_index',
            ));
        }

        return $menu;
    }
}
