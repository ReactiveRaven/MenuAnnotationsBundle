<?php
namespace RRaven\Bundle\MenuAnnotationsBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Mopa\Bundle\BootstrapBundle\Navbar\AbstractNavbarMenuBuilder;

class NavbarMenuBuilder extends AbstractNavbarMenuBuilder
{
    protected $securityContext;
    protected $isLoggedIn;

    public function __construct(FactoryInterface $factory, SecurityContextInterface $securityContext)
    {
        parent::__construct($factory);

        $this->securityContext = $securityContext;
        $this->isLoggedIn = $this->securityContext->isGranted('IS_AUTHENTICATED_FULLY');
    }
    
    /**
     * 
     * @param MenuItem $root
     * @param array $array
     * @param boolean $isRoot
     */
    private function arrayToMenu($root, $array, $isRoot = true) {
      foreach ($array as $key => $value) {
        if (is_array($value)) {
          if (count($value)) {
            if ($isRoot) {
              $item = $this->createDropdownMenuItem($root, $key, false);
              $this->arrayToMenu($item, $value, false);
            } else {
              $item = $this->createSubDropdownMenuItem($root, $key, false);
              $this->arrayToMenu($item, $value, false);
            }
          }
        } else {
          $root->addChild($key, array("route" => str_replace("@", "", $value)));
        }
      }
      return $root;
    }

    public function createMainMenu(Request $request)
    {
        global $kernel;
        /* @var $kernel \AppKernel */
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav');
        
        $menumenu =  $kernel->getContainer()->get("rraven.helper.menumenu");
        /* @var $menumenu \RRaven\Bundle\MenuAnnotationsBundle\Helper\MenuMenuHelper */
        $menu_array = $menumenu->sniffMenus();
        
        $this->arrayToMenu($menu, $menu_array);

        return $menu;
    }
    
    private function createSubDropdownMenuItem($rootItem, $title, $push_right = true, $icon = array(), $knp_item_options = array()) {
        $rootItem
            ->setAttribute('class', 'nav')
        ;
        if ($push_right) {
            $this->pushRight($rootItem);
        }
        $dropdown = $rootItem->addChild($title, array_merge($knp_item_options, array('uri'=>'#')))
            ->setLinkattribute('class', 'dropdown-toggle')
            ->setLinkattribute('data-toggle', 'dropdown')
            ->setAttribute('class', 'dropdown')
            ->setChildrenAttribute('class', 'dropdown-menu sub-menu')
        ;
        // TODO: make XSS safe $icon contents escaping
        if (isset($icon['icon'])) {
            $icon = array_merge(array('tag'=>'i'), $icon);
            $dropdown->setLabel($title. ' <'.$icon['tag'].' class="'.$icon['icon'].'"></'.$icon['tag'].'>')
                     ->setExtra('safe_label', true);
        }

        return $dropdown;
    }

    public function createRightSideDropdownMenu(Request $request)
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav pull-right');

        if ($this->isLoggedIn) {
            $menu->addChild('Logout', array('route' => 'fos_user_security_logout'));
        } else {
            $menu->addChild('Login', array('route' => 'hwi_oauth_connect'));
        }

        //$this->addDivider($menu, true);
        //$menu->addChild('Impressum', array('route' => 'impressum'));

        return $menu;
    }
}