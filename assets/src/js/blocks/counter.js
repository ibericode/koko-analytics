const { __ } = window.wp.i18n;
const { registerBlockType } = window.wp.blocks
const createElement = window.wp.element.createElement;
const { useBlockProps, InspectorControls } = window.wp.blockEditor
const { PanelBody } = window.wp.components

// TODO: Add interface for global or current post
// TODO: Add interface for number of days to lookback
// TODO: Add interface for choosing visitors or pageviews
// TODO: Add interface for setting text

registerBlockType('koko-analytics/counter', {
  apiVersion: 3,
  title: __('Pageview Count'),
  description: __('Displays the total number of pageviews for this post or page.'),
  category: 'text',
  attributes: {},
  supports: {},
  edit: function ({ attributes, setAttributes } ) {
      /**
       * Create the panel body for the settings
       * Includes the text control
       * Will be added to the InspectorControls
       */
      const panelBody = createElement(
        PanelBody,
        {
          title: __( 'Settings', 'copyright-date-block' ),
        }
      );

      /**
       * Create the inspector controls for the block
       * Includes the panel body
       * Will be added to the final block output
       */
      const inspectorControls = createElement(
        InspectorControls,
        {},
        panelBody
      );

      /**
       * Create the paragraph with the copyright information in it
       * Will be added to the final block output
       */
      const paragraph = createElement('p', {},
          "This page has been viewed a total of ",
          createElement('strong', { 'className' : 'koko-analytics-counter'}, "0"),
          " times."
        );

      /**
       * Create the final block output
       * Includes the block controls, inspector controls, and the paragraph with the copyright information
       */
      return createElement(
        'div',
        useBlockProps(),
        inspectorControls,
        paragraph,
      );
  },
  save: function (props) {
    return null
  }
})
