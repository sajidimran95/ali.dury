<?php

namespace App\Http;

use Collective\Html\HtmlFacade as HTML;
use Illuminate\Support\Arr;
use Nwidart\Menus\Presenters\Presenter;

class AdminlteCustomPresenter extends Presenter
{
    /**
     * {@inheritdoc}.
     */
    public function getOpenTagWrapper()
    {
        return '<div class="zoho-sidebar-nav" id="side-bar">' . PHP_EOL;
    }

    /**
     * {@inheritdoc}.
     */
    public function getCloseTagWrapper()
    {
        return '</div>' . PHP_EOL;
    }

    /**
     * {@inheritdoc}.
     */
    public function getMenuWithoutDropdownWrapper($item)
    {
        $link = '<a href="' . $item->getUrl() . '" title="' . e($item->title) . '" class="tw-flex tw-w-full tw-items-center tw-gap-2.5 tw-px-3 tw-py-1.5 tw-text-sm tw-font-normal tw-transition-all tw-duration-200 tw-rounded-lg tw-whitespace-nowrap theme-sidebar-hover' . $this->getActiveState($item) . '" ' . $item->getAttributes() . '>' .
        $this->formatIcon($item->icon) . ' <span class="sidebar-menu-label tw-truncate tw-flex-1 tw-min-w-0">' . $item->title . '</span>' .
        '<span class="sidebar-menu-chevron sidebar-menu-chevron--spacer" aria-hidden="true"></span>' .
            '</a>';

        return '<div class="zoho-sidebar-item zoho-sidebar-item--leaf">' . $link . PHP_EOL . '</div>' . PHP_EOL;
    }

    /**
     * {@inheritdoc}.
     */
    public function getActiveState($item, $state = ' theme-sidebar-active')
    {
        return $item->isActive() ? $state : null;
    }

    /**
     * Get active state on child items.
     *
     * @param  \Nwidart\Menus\MenuItem  $item
     * @param  string  $state
     * @return null|string
     */
    public function getActiveStateOnChild($item, $state = ' theme-sidebar-active')
    {
        return $item->hasActiveOnChild() ? $state : null;
    }

    /**
     * {@inheritdoc}.
     */
    public function getDividerWrapper()
    {
        return '<div class="tw-my-2"></div>';
    }

    /**
     * {@inheritdoc}.
     */
    public function getHeaderWrapper($item)
    {
        return '<div class="zoho-sidebar-section zoho-sidebar-submenu-header tw-px-1 tw-pt-2 tw-pb-0.5 tw-text-[10px] tw-font-semibold tw-uppercase tw-tracking-wider tw-text-gray-400">' . e($item->title) . '</div>';
    }

    /**
     * {@inheritdoc}.
     */
    public function getMenuWithDropDownWrapper($item)
    {
        $dropdownToggle = '<a href="#" title="' . e($item->title) . '" class="drop_down tw-flex tw-w-full tw-items-center tw-gap-2.5 tw-px-3 tw-py-1.5 tw-text-sm tw-font-normal tw-transition-all tw-duration-200 tw-rounded-lg tw-whitespace-nowrap theme-sidebar-hover' . $this->getActiveStateOnChild($item) . '" ' . $item->getAttributes() . '>' .
        $this->formatIcon($item->icon) . ' <span class="sidebar-menu-label tw-truncate tw-flex-1 tw-min-w-0">' . $item->title . '</span>' .
        '<svg aria-hidden="true" class="sidebar-menu-chevron svg tw-ml-auto tw-size-4 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">' . $this->getArray($item) .
            '</svg>' .
            '</a>';

        $childItems = $this->getChildMenuItems($item);

        return '<div class="zoho-sidebar-item">' . $dropdownToggle . $childItems . '</div>' . PHP_EOL;
    }

    /**
     * Nested submenu under Inventory (Items / Stock).
     *
     * @param  \Nwidart\Menus\MenuItem  $item
     * @return string
     */
    public function getMultiLevelDropdownWrapper($item)
    {
        $open = $item->hasActiveOnChild();
        $display = $open ? 'block' : 'none';
        $activeClass = $open ? ' theme-sidebar-child-active' : '';

        $toggle = '<a href="#" title="' . e($item->title) . '" class="zoho-sidebar-nested-toggle drop_down tw-flex tw-w-full tw-items-center tw-justify-between tw-text-sm tw-font-medium tw-text-gray-700 tw-py-1.5 tw-px-1 tw-rounded-md theme-sidebar-child-hover' . $activeClass . '">' .
            '<span>' . e($item->title) . '</span>' .
            '<svg aria-hidden="true" class="sidebar-menu-chevron tw-size-3.5 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">' .
            $this->getArray($item) .
            '</svg></a>';

        $body = '<div class="chiled zoho-sidebar-nested tw-mt-1 tw-mb-2 tw-pl-3 tw-space-y-1" style="display:' . $display . '" data-menu-title="' . e($item->title) . '">';
        foreach ($item->getChilds() as $child) {
            if (method_exists($child, 'hidden') && $child->hidden()) {
                continue;
            }
            if ($child->isHeader()) {
                $body .= $this->getHeaderWrapper($child) . PHP_EOL;
            } elseif ($child->hasSubMenu()) {
                $body .= $this->getMultiLevelDropdownWrapper($child) . PHP_EOL;
            } else {
                $body .= $this->renderChildMenuRow($child) . PHP_EOL;
            }
        }
        $body .= '</div>';

        return '<div class="zoho-sidebar-nested-group">' . $toggle . $body . '</div>';
    }

    /**
     * Get child menu items.
     *
     * @param  \Nwidart\Menus\MenuItem  $item
     * @return string
     */
    public function getChildMenuItems($item)
    {
        $children = '';
        $displayStyle = $item->hasActiveOnChild() ? 'block' : 'none';

        if (count($item->getChilds()) > 0) {
            $children .= '<div class="chiled tw-relative tw-mt-1 tw-mb-2 tw-pl-11" style="display:' . $displayStyle . '" data-menu-title="' . e($item->title) . '">
            <div class="zoho-sidebar-flyout__header">' . e(strtoupper($item->title)) . '</div>
            <div class="tw-absolute tw-inset-y-0 tw-w-px tw-h-full tw-bg-gray-100 tw-left-5 zoho-sidebar-flyout__rail"></div>
            <div class="zoho-sidebar-flyout__body tw-space-y-1">';

            foreach ($item->getChilds() as $child) {
                if (method_exists($child, 'hidden') && $child->hidden()) {
                    continue;
                }

                if ($child->isHeader()) {
                    $children .= $this->getHeaderWrapper($child) . PHP_EOL;
                } elseif ($child->isDivider()) {
                    $children .= $this->getDividerWrapper() . PHP_EOL;
                } elseif ($child->hasSubMenu()) {
                    $children .= $this->getMultiLevelDropdownWrapper($child) . PHP_EOL;
                } else {
                    $children .= $this->renderChildMenuRow($child) . PHP_EOL;
                }
            }

            $children .= '</div></div>';
        }

        return $children;
    }

    /**
     * @param  string  $icon
     * @return string
     */
    protected function formatIcon($icon)
    {
        $icon = (string) $icon;

        if (strpos($icon, '<svg') !== false) {
            return '<span class="zoho-sidebar-menu-icon" aria-hidden="true">' . $icon . '</span>';
        }

        return '<span class="zoho-sidebar-menu-icon" aria-hidden="true"><i class="' .
            e($icon) . '"></i></span>';
    }

    public function getArray($item)
    {
        if ($item->hasActiveOnChild()) {
            return '<path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M6 9l6 6l6 -6" />';
        }

        return '<path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M15 6l-6 6l6 6" />';
    }

    /**
     * @param  \Nwidart\Menus\MenuItem  $child
     * @return string
     */
    protected function renderChildMenuRow($child)
    {
        $isActive = $child->isActive() ? 'theme-sidebar-child-active' : '';
        $quickAdd = $this->renderQuickAddButton($child);

        return '<div class="zoho-sidebar-child-row' . ($isActive ? ' zoho-sidebar-child-row--active' : '') . ($quickAdd ? ' zoho-sidebar-child-row--has-add' : '') . '">' .
            '<a href="' . $child->getUrl() . '" title="' . e($child->title) . '" class="zoho-sidebar-child-link tw-flex tw-flex-1 tw-min-w-0 tw-text-sm tw-font-normal tw-text-gray-600 tw-truncate tw-transition-all tw-duration-200 tw-py-1 theme-sidebar-child-hover tw-whitespace-nowrap ' . $isActive . '" ' . $this->getChildLinkAttributes($child) . '>' .
            '<span>' . $child->title . '</span>' .
            '</a>' .
            $quickAdd .
            '</div>';
    }

    /**
     * @param  \Nwidart\Menus\MenuItem  $child
     * @return string
     */
    protected function renderQuickAddButton($child)
    {
        $attributes = $child->attributes ?? [];
        $addUrl = Arr::get($attributes, 'add_url');
        $addModal = Arr::get($attributes, 'add_modal');
        $addLabel = Arr::get($attributes, 'add_label', __('messages.add'));

        if (empty($addUrl) && empty($addModal)) {
            return '';
        }

        $title = ' title="' . e($addLabel) . '" aria-label="' . e($addLabel) . '"';

        if (! empty($addModal)) {
            $href = Arr::get($addModal, 'href', '#');
            $container = Arr::get($addModal, 'container', '#global_contact_modal');

            return '<a href="#" class="zoho-sidebar-child-add btn-modal"' . $title .
                ' data-href="' . e($href) . '" data-container="' . e($container) . '">' .
                '<span aria-hidden="true">+</span></a>';
        }

        return '<a href="' . e($addUrl) . '" class="zoho-sidebar-child-add"' . $title . '>' .
            '<span aria-hidden="true">+</span></a>';
    }

    /**
     * @param  \Nwidart\Menus\MenuItem  $child
     * @return string
     */
    protected function getChildLinkAttributes($child)
    {
        $attributes = $child->attributes ?? [];
        Arr::forget($attributes, ['active', 'icon', 'add_url', 'add_modal', 'add_label']);

        return HTML::attributes($attributes);
    }
}
