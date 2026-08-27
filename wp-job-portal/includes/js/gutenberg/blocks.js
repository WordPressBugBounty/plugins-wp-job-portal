(function (wp) {
    'use strict';

    if (!wp || !wp.blocks || !wp.element || !wp.components || !wp.i18n) {
        return;
    }

    var el = wp.element.createElement;
    var Fragment = wp.element.Fragment;
    var registerBlockType = wp.blocks.registerBlockType;
    var blockEditor = wp.blockEditor || wp.editor || {};
    var InspectorControls = blockEditor.InspectorControls;
    var useBlockProps = blockEditor.useBlockProps;
    var PanelBody = wp.components.PanelBody;
    var TextControl = wp.components.TextControl;
    var SelectControl = wp.components.SelectControl;
    var ToggleControl = wp.components.ToggleControl;
    var Notice = wp.components.Notice;
    var ServerSideRender = wp.serverSideRender || wp.components.ServerSideRender;
    var __ = wp.i18n.__;

    if (!InspectorControls || !ServerSideRender) {
        return;
    }

    function editorWrapperProps(className) {
        if (useBlockProps) {
            return useBlockProps({ className: 'wpjobportal-block-editor-shell ' + className });
        }

        return { className: 'wpjobportal-block-editor-shell ' + className };
    }

    function setTextAttribute(props, attribute) {
        return function (value) {
            var update = {};
            update[attribute] = value;
            props.setAttributes(update);
        };
    }

    function setBoundedNumberAttribute(props, attribute, minimum, maximum) {
        return function (value) {
            var parsed = parseInt(value, 10);
            var update = {};

            if (value === '') {
                update[attribute] = '';
            } else if (!isNaN(parsed)) {
                update[attribute] = String(Math.min(maximum, Math.max(minimum, parsed)));
            }

            props.setAttributes(update);
        };
    }

    function setToggleAttribute(props, attribute) {
        return function (enabled) {
            var update = {};
            update[attribute] = enabled ? '1' : '0';
            props.setAttributes(update);
        };
    }

    function renderPreview(blockName, props, className) {
        return el(
            'div',
            editorWrapperProps(className),
            el(ServerSideRender, {
                block: blockName,
                attributes: props.attributes,
                EmptyResponsePlaceholder: function () {
                    return el(
                        Notice,
                        { status: 'info', isDismissible: false },
                        __('No matching content is available for this preview yet.', 'wp-job-portal')
                    );
                }
            })
        );
    }

    function listingEdit(config, props) {
        return el(
            Fragment,
            {},
            el(
                InspectorControls,
                {},
                el(
                    PanelBody,
                    { title: __('Content', 'wp-job-portal'), initialOpen: true },
                    el(TextControl, {
                        label: __('Title', 'wp-job-portal'),
                        value: props.attributes.title || '',
                        onChange: setTextAttribute(props, 'title')
                    }),
                    el(SelectControl, {
                        label: config.typeLabel,
                        value: props.attributes[config.typeAttribute] || config.defaultType,
                        options: config.typeOptions,
                        onChange: setTextAttribute(props, config.typeAttribute)
                    }),
                    el(TextControl, {
                        type: 'number',
                        min: 1,
                        max: 50,
                        label: config.countLabel,
                        help: __('Choose a value from 1 to 50.', 'wp-job-portal'),
                        value: props.attributes[config.countAttribute] || '',
                        onChange: setBoundedNumberAttribute(props, config.countAttribute, 1, 50)
                    })
                ),
                el(
                    PanelBody,
                    { title: __('Layout', 'wp-job-portal'), initialOpen: false },
                    el(SelectControl, {
                        label: __('Columns', 'wp-job-portal'),
                        value: props.attributes.fieldcolumn || '1',
                        options: [1, 2, 3, 4, 5].map(function (number) {
                            return { value: String(number), label: String(number) };
                        }),
                        onChange: setTextAttribute(props, 'fieldcolumn')
                    }),
                    el(SelectControl, {
                        label: __('Listing style', 'wp-job-portal'),
                        value: props.attributes.listingstyle || '1',
                        options: [
                            { value: '1', label: __('Default', 'wp-job-portal') },
                            { value: '0', label: __('Compact', 'wp-job-portal') }
                        ],
                        onChange: setTextAttribute(props, 'listingstyle')
                    })
                )
            ),
            renderPreview(config.name, props, config.className)
        );
    }

    function registerListingBlock(config) {
        var attributes = {
            title: { type: 'string', default: config.defaultTitle },
            fieldcolumn: { type: 'string', default: '1' },
            listingstyle: { type: 'string', default: '1' }
        };
        attributes[config.typeAttribute] = { type: 'string', default: config.defaultType };
        attributes[config.countAttribute] = { type: 'string', default: config.defaultCount };

        registerBlockType(config.name, {
            apiVersion: 2,
            title: config.title,
            description: config.description,
            icon: config.icon,
            category: 'wp-job-portal',
            keywords: [__('jobs', 'wp-job-portal'), __('careers', 'wp-job-portal')],
            attributes: attributes,
            supports: { html: false, align: ['wide', 'full'] },
            edit: function (props) {
                return listingEdit(config, props);
            },
            save: function () {
                return null;
            }
        });
    }

    registerListingBlock({
        name: 'wpjobportal/wpjobportaljobsblock',
        title: __('Jobs', 'wp-job-portal'),
        description: __('Display a configurable list of jobs.', 'wp-job-portal'),
        icon: 'businessperson',
        className: 'wpjobportal-jobs-block-preview',
        defaultTitle: __('Newest Jobs', 'wp-job-portal'),
        defaultType: '1',
        defaultCount: '5',
        typeAttribute: 'typeofjobs',
        countAttribute: 'noofjobs',
        typeLabel: __('Job selection', 'wp-job-portal'),
        countLabel: __('Number of jobs', 'wp-job-portal'),
        typeOptions: [
            { value: '1', label: __('Newest jobs', 'wp-job-portal') },
            { value: '2', label: __('Top jobs', 'wp-job-portal') },
            { value: '3', label: __('Hot jobs', 'wp-job-portal') },
            { value: '5', label: __('Featured jobs', 'wp-job-portal') }
        ]
    });

    registerListingBlock({
        name: 'wpjobportal/wpjobportalcompaniesblock',
        title: __('Companies', 'wp-job-portal'),
        description: __('Display featured companies.', 'wp-job-portal'),
        icon: 'building',
        className: 'wpjobportal-companies-block-preview',
        defaultTitle: __('Featured Companies', 'wp-job-portal'),
        defaultType: '2',
        defaultCount: '5',
        typeAttribute: 'companytype',
        countAttribute: 'noofcompanies',
        typeLabel: __('Company selection', 'wp-job-portal'),
        countLabel: __('Number of companies', 'wp-job-portal'),
        typeOptions: [
            { value: '2', label: __('Featured companies', 'wp-job-portal') }
        ]
    });

    registerListingBlock({
        name: 'wpjobportal/wpjobportalresumesblock',
        title: __('Resumes', 'wp-job-portal'),
        description: __('Display a configurable list of resumes.', 'wp-job-portal'),
        icon: 'id',
        className: 'wpjobportal-resumes-block-preview',
        defaultTitle: __('Latest Resumes', 'wp-job-portal'),
        defaultType: '1',
        defaultCount: '5',
        typeAttribute: 'typeofresume',
        countAttribute: 'noofresumes',
        typeLabel: __('Resume selection', 'wp-job-portal'),
        countLabel: __('Number of resumes', 'wp-job-portal'),
        typeOptions: [
            { value: '1', label: __('Newest resumes', 'wp-job-portal') },
            { value: '2', label: __('Top resumes', 'wp-job-portal') },
            { value: '4', label: __('Featured resumes', 'wp-job-portal') }
        ]
    });

    var jobSearchToggles = [
        ['showtitle', __('Search heading', 'wp-job-portal')],
        ['jobtitle', __('Job title', 'wp-job-portal')],
        ['category', __('Category', 'wp-job-portal')],
        ['jobtype', __('Job type', 'wp-job-portal')],
        ['jobstatus', __('Job status', 'wp-job-portal')],
        ['salaryrange', __('Salary range', 'wp-job-portal')],
        ['shift', __('Shift', 'wp-job-portal')],
        ['duration', __('Duration', 'wp-job-portal')],
        ['startpublishing', __('Start publishing', 'wp-job-portal')],
        ['stoppublishing', __('Stop publishing', 'wp-job-portal')],
        ['company', __('Company', 'wp-job-portal')],
        ['address', __('Location', 'wp-job-portal')]
    ];

    var jobSearchAttributes = {
        title: { type: 'string', default: __('Job Search', 'wp-job-portal') },
        columnperrow: { type: 'string', default: '1' }
    };
    jobSearchToggles.forEach(function (item) {
        jobSearchAttributes[item[0]] = { type: 'string', default: '1' };
    });

    registerBlockType('wpjobportal/wpjobportaljobsearchblock', {
        apiVersion: 2,
        title: __('Job Search', 'wp-job-portal'),
        description: __('Add a configurable job search form.', 'wp-job-portal'),
        icon: 'search',
        category: 'wp-job-portal',
        keywords: [__('jobs', 'wp-job-portal'), __('search', 'wp-job-portal')],
        attributes: jobSearchAttributes,
        supports: { html: false, align: ['wide', 'full'] },
        edit: function (props) {
            return el(
                Fragment,
                {},
                el(
                    InspectorControls,
                    {},
                    el(
                        PanelBody,
                        { title: __('Content', 'wp-job-portal'), initialOpen: true },
                        el(TextControl, {
                            label: __('Title', 'wp-job-portal'),
                            value: props.attributes.title || '',
                            onChange: setTextAttribute(props, 'title')
                        }),
                        el(SelectControl, {
                            label: __('Columns per row', 'wp-job-portal'),
                            value: props.attributes.columnperrow || '1',
                            options: [1, 2, 3, 4].map(function (number) {
                                return { value: String(number), label: String(number) };
                            }),
                            onChange: setTextAttribute(props, 'columnperrow')
                        })
                    ),
                    el.apply(
                        null,
                        [PanelBody, { title: __('Visible fields', 'wp-job-portal'), initialOpen: false }].concat(
                            jobSearchToggles.map(function (item) {
                                return el(ToggleControl, {
                                    key: item[0],
                                    label: item[1],
                                    checked: props.attributes[item[0]] !== '0',
                                    onChange: setToggleAttribute(props, item[0])
                                });
                            })
                        )
                    )
                ),
                renderPreview('wpjobportal/wpjobportaljobsearchblock', props, 'wpjobportal-job-search-block-preview')
            );
        },
        save: function () {
            return null;
        }
    });
}(window.wp));
