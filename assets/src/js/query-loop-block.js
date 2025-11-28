const MY_VARIATION_NAME = 'koko-analytics/most-viewed-pages';

window.wp.blocks.registerBlockVariation( 'core/query', {
    apiVersion: 3,
    name: MY_VARIATION_NAME,
    title: 'Most Viewed Post Type',
    description: 'Displays a list of your most viewed posts, pages or other post types.',
    isActive: [ 'namespace' ],
    icon: '',
    attributes: {
        namespace: MY_VARIATION_NAME,
    },
    scope: [ 'inserter' ],
    allowedControls: [ 'postType'],
    }
);
