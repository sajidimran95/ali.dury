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
        return '<div class="tw-flex-1 tw-overflow-y-auto tw-min-h-0" id="side-bar">' . PHP_EOL;
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
        $link = '<a href="' . $item->getUrl() . '" title="' . e($item->title) . '" class="tw-flex tw-w-full tw-items-center tw-gap-2.5 tw-px-3 tw-py-1.5 tw-text-sm tw-font-normal tw-text-gray-600 tw-transition-all tw-duration-200 tw-rounded-lg tw-whitespace-nowrap theme-sidebar-hover' . $this->getActiveState($item) . '" ' . $item->getAttributes() . '>' .
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
     * @param $item
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
        // Assuming a divider is just a visual space in this design
        return '<div class="tw-my-2"></div>';
    }

    /**
     * {@inheritdoc}.
     */
    public function getHeaderWrapper($item)
    {
        return '<div class="tw-px-3 tw-pt-4 tw-pb-1 tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wider tw-text-gray-400">' . $item->title . '</div>';
    }

    /**
     * {@inheritdoc}.
     */
    public function getMenuWithDropDownWrapper($item)
    {
        $dropdownToggle = '<a href="#" title="' . e($item->title) . '" class="drop_down tw-flex tw-w-full tw-items-center tw-gap-2.5 tw-px-3 tw-py-1.5 tw-text-sm tw-font-normal tw-text-gray-600 tw-transition-all tw-duration-200 tw-rounded-lg tw-whitespace-nowrap theme-sidebar-hover' . $this->getActiveStateOnChild($item) . '" ' . $item->getAttributes() . '>' .
        $this->formatIcon($item->icon) . ' <span class="sidebar-menu-label tw-truncate tw-flex-1 tw-min-w-0">' . $item->title . '</span>' .
        '<svg aria-hidden="true" class="sidebar-menu-chevron svg tw-ml-auto tw-text-gray-500 tw-size-4 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">' . $this->getArray($item) .
            '</svg>' .
            '</a>';

        $childItemsContainerStart = '';

        $childItemsContainerEnd = '';

        // Compile child menu items
        $childItems = $this->getChildMenuItems($item);

        // echo "here";
        // print_r($dropdownToggle);exit;

        return '<div class="zoho-sidebar-item">' . $dropdownToggle . $childItemsContainerStart . $childItems . $childItemsContainerEnd . '</div>' . PHP_EOL;
    }

    /**
     * Get multi-level dropdown wrapper.
     *
     * Note: This example doesn't directly implement a multi-level dropdown, as it wasn't specified, but you could extend
     * the functionality similarly to `getMenuWithDropDownWrapper`, adjusting for deeper nesting.
     *
     * @param  \Nwidart\Menus\MenuItem  $item
     * @return string
     */
    public function getMultiLevelDropdownWrapper($item)
    {
        // Placeholder for multi-level dropdown functionality if needed
        return '';
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
                $children .= $this->renderChildMenuRow($child) . PHP_EOL;
            }

            $children .= '</div></div>';
        }

        return $children;
    }

    /**
     * Returns the icon HTML. If the icon is SVG, it returns directly; otherwise, it assumes it's a FontAwesome class and wraps it in an <i> tag.
     *
     * @param string $icon
     * @return string
     */
    protected function formatIcon($icon)
    {
        // Check if the icon string contains "<svg", indicating it's an SVG icon
        if (strpos($icon, '<svg') !== false) {
            return $icon; // Return the SVG icon directly
        } else {
            // Assume it's a FontAwesome icon and return it wrapped in an <i> tag
            return '<i class="' . $icon . ' tw-size-5 tw-shrink-0"></i>';
        }
    }

    public function getArray($item)
    {
        if ($item->hasActiveOnChild()) {
            return '<path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M6 9l6 6l6 -6" />';
        } else {
            return '<path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M15 6l-6 6l6 6" />';
        }
    }

    /**
     * Render a submenu row (list link + optional Zoho-style quick-add button).
     *
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


