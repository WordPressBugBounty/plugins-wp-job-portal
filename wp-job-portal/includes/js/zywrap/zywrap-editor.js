(function(wp) {
    const { addFilter } = wp.hooks;
    const { createHigherOrderComponent } = wp.compose;
    const { BlockControls } = wp.blockEditor;
    const { ToolbarGroup, ToolbarButton } = wp.components;
    const { createElement, Fragment } = wp.element;

    // 1. Define Your Icon
    const ZywrapIcon = createElement('svg', { 
    width: 20, 
    height: 20, 
    viewBox: '0 0 100 100', 
    // Set fill to none globally so it doesn't show as a solid block
    fill: 'none', 
    xmlns: 'http://www.w3.org/2000/svg'
},
    // Outer Hexagon
    createElement('path', { 
        d: 'M50 10 L85 28 L85 72 L50 90 L15 72 L15 28 Z', 
        stroke: 'currentColor', // Use the text color for the lines
        strokeWidth: '6', 
        strokeLinejoin: 'round', 
        strokeLinecap: 'round',
        fill: 'none' // Explicitly no fill
    }),
    // Brain/Cloud Shape (Dashed line)
    createElement('path', { 
        d: 'M50 25 C65 25 75 35 75 50 C75 65 65 75 50 75 C35 75 25 65 25 50 C25 35 35 25 50 25', 
        stroke: 'currentColor',
        strokeWidth: '6', 
        strokeLinecap: 'round', 
        strokeLinejoin: 'round', 
        strokeDasharray: '10, 5',
        fill: 'none' // Explicitly no fill
    }),
    // Text AI
    createElement('text', { 
        x: '50', 
        y: '62', // Adjusted Y slightly for better centering
        fontFamily: 'Arial, sans-serif', 
        fontWeight: 'bold', 
        fontSize: '28', 
        textAnchor: 'middle', 
        fill: 'currentColor', // The text MUST have a fill to be visible
        stroke: 'none' // Text should not have an outline
    }, 'AI')
);

    // 2. The Trigger Function (Opens your jQuery Modal)
    const openStudio = () => {
        // Try to click the hidden classic button first
        const modalBtn = document.getElementById('zywrap-open-modal-button');
        if (modalBtn) {
            modalBtn.click();
        } else {
            // Fallback: manually trigger the backdrop/wrap if button isn't found
            if (window.jQuery) {
                window.jQuery('#zywrap-classic-modal-backdrop').show();
                window.jQuery('#zywrap-classic-modal-wrap').css('display', 'flex');
            }
        }
    };

    // 3. Create the Component to Inject into the Toolbar
    const withZywrapToolbar = createHigherOrderComponent((BlockEdit) => {
        return (props) => {
            // Optional: Limit to specific blocks (e.g., only paragraphs and headings)
            // if (props.name !== 'core/paragraph' && props.name !== 'core/heading') {
            //    return createElement(BlockEdit, props);
            // }

            return createElement(
                Fragment,
                {},
                createElement(BlockEdit, props), // Render the original block
                createElement(
                    BlockControls,
                    { group: 'other' }, // Group 'other' places it towards the end
                    createElement(
                        ToolbarGroup,
                        {},
                        createElement(ToolbarButton, {
                            icon: ZywrapIcon,
                            label: 'Open AI Studio', // Tooltip text
                            onClick: openStudio,
                        })
                    )
                )
            );
        };
    }, 'withZywrapToolbar');

    // 4. Register the Filter
    addFilter(
        'editor.BlockEdit',
        'wpjobportal/zywrap-toolbar',
        withZywrapToolbar
    );

})(window.wp);