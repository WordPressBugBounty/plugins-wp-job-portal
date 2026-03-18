(function($) {
    'use strict';

    var globalWrapperData = [];
    var allWrappers = [];
    var searchTimer;
    var searchSelectedId = null; 

    $(function() {
        if (typeof wpjpzywrapClassicData === 'undefined') return;

        const { 
            categories, models, languages, templates, 
            wrappers_nonce, all_wrappers_nonce, execute_nonce, ajax_url, 
            loading_text, generating_text, run_text 
        } = wpjpzywrapClassicData;

        // Elements
        const $modalBackdrop    = $('#zywrap-classic-modal-backdrop');
        const $modalWrap        = $('#zywrap-classic-modal-wrap');
        const $wrapperSelect    = $('#zywrap-classic-wrapper');
        const $categorySelect   = $('#zywrap-classic-category');
        const $descBox          = $('#zywrap-classic-description');
        const $responseArea     = $('#zywrap-classic-response-area');
        const $insertBtn        = $('#zywrap-classic-insert-btn');
        const $generateBtn      = $('#zywrap-classic-run');
        const $searchWrapper    = $('#zywrap-search-wrapper');
        const $searchInput      = $('#zywrap-classic-search-input');
        const $searchSelect     = $('#zywrap-classic-search-select');
        const $resultsContainer = $('#zywrap-search-results-container');
        const $fieldfor         = $('#wpjobportal_content_generation_fieldfor');

        // --- 1. POPULATE DROPDOWNS ---
        $categorySelect.append(new Option('-- Select Category --', ''));
        if(categories) categories.forEach(c => $categorySelect.append(new Option(c.name, c.code)));

        const $modelSelect = $('#zywrap-classic-model');
        $modelSelect.append(new Option('-- Default --', ''));
        if(models) models.forEach(m => $modelSelect.append(new Option(m.name, m.code)));

        const $langSelect = $('#zywrap-classic-language');
        $langSelect.append(new Option('-- English --', ''));
        if(languages) languages.forEach(l => $langSelect.append(new Option(l.name, l.code)));

        const overrideMap = {
            toneCode: { label: 'Tone', data: templates.tones },
            styleCode: { label: 'Style', data: templates.styles },
            formatCode: { label: 'Format', data: templates.formattings },
            complexityCode: { label: 'Complexity', data: templates.complexities },
            lengthCode: { label: 'Length', data: templates.lengths },
            audienceCode: { label: 'Audience', data: templates.audienceLevels },
            responseGoalCode: { label: 'Goal', data: templates.responseGoals },
            outputCode: { label: 'Output', data: templates.outputTypes }
        };

        const $overridesGrid = $('#zywrap-classic-overrides-grid');
        $.each(overrideMap, function(id, field) {
            var $container = $('<div>');
            $container.append('<label class="wpjp-zywrap-label">' + field.label + '</label>');
            var $sel = $('<select class="wpjp-zywrap-jp-chosen zywrap-classic-override-select"></select>').attr('data-original-id', id);
            $sel.append(new Option(field.label + ' (Default)', ''));
            if (field.data) field.data.forEach(o => $sel.append(new Option(o.name, o.code)));
            $container.append($sel);
            $overridesGrid.append($container);
        });

        $('.wpjp-zywrap-jp-chosen').chosen({ width: '100%', search_contains: true });

        // --- 2. DELEGATED SEARCH LOGIC (FIXED FOR DOUBLE TRIGGER) ---
        // .off('click') ensures we don't stack listeners if Gutenberg re-runs the script
        $(document).off('click', '#zywrap-search-toggle-btn').on('click', '#zywrap-search-toggle-btn', function(e) {
            e.preventDefault();
            e.stopPropagation(); 
            
            // Stop any current animation to prevent snapping back
            $searchWrapper.stop(true, true);

            if ($searchWrapper.is(':visible')) {
                $searchWrapper.slideUp(250);
                $('#zywrap-search-toggle-text').text('Search Templates');
                $searchInput.val('');
                $resultsContainer.hide();
                $searchSelect.empty().append(new Option('-- Search Results --', '')).trigger('chosen:updated');
            } else {
                $searchWrapper.slideDown(250);
                $('#zywrap-search-toggle-text').text('Close Search');
                if(globalWrapperData.length === 0) populateSearchData();
            }
        });

        function populateSearchData(){
            $.post(ajax_url, { 
                action: 'wpjobportal_ajax', wpjobportalme: 'zywrap', task: 'getZywrapAllWrappers', _wpnonce: all_wrappers_nonce 
            }, function(response) {
                if (response.success) globalWrapperData = response.data;
            });
        }

        $(document).off('keyup', '#zywrap-classic-search-input').on('keyup', '#zywrap-classic-search-input', function() {
            clearTimeout(searchTimer);
            var searchTerm = $(this).val().toLowerCase();
            if (searchTerm.length < 3) return;

            searchTimer = setTimeout(function() {
                var filtered = [];
                var count = 0;
                var limit = 100;

                for (var i = 0; i < globalWrapperData.length; i++) {
                    if (count >= limit) break;
                    var item = globalWrapperData[i];
                    var name = (item[1] || '').toLowerCase();
                    var code = (item[0] || '').toLowerCase(); 
                    if (name.indexOf(searchTerm) > -1 || code.indexOf(searchTerm) > -1) {
                        filtered.push(item); count++;
                    }
                }

                $searchSelect.empty();
                var msg = filtered.length === 0 ? 'No record Found.' : filtered.length + '+ records Found.';
                $searchSelect.append('<option value="">' + msg + '</option>');
                $.each(filtered, function(index, item) {
                    var $opt = $('<option>', { value: item[0], text: item[1], 'data-category': item[2] });
                    $searchSelect.append($opt);
                });
                $resultsContainer.show();
                $searchSelect.trigger('chosen:updated');
            }, 250);
        });

        $(document).off('change', '#zywrap-classic-search-select').on('change', '#zywrap-classic-search-select', function() {
            var selectedOption = $(this).find(':selected');
            var catCode = selectedOption.data('category');
            var selectedId = $(this).val();
            if(catCode) {
                searchSelectedId = selectedId;
                $categorySelect.val(catCode).trigger('chosen:updated').trigger('change');
                // Trigger click on the toggle button to close the search drawer
                $('#zywrap-search-toggle-btn').trigger('click');
            }
        });

        // --- 3. CATEGORY & WRAPPER SELECTION ---
        $(document).off('change', '#zywrap-classic-category').on('change', '#zywrap-classic-category', function() {
            $('#zywrap-classic-loader').show();
            var cat = $(this).val();
            $wrapperSelect.empty().append(new Option(loading_text, '')).prop('disabled', true).trigger('chosen:updated');
            if (!cat) return;
            $.post(ajax_url, { 
                action: 'wpjobportal_ajax', wpjobportalme: 'zywrap', task: 'getWrappersByCategory', category_code: cat, _wpnonce: wrappers_nonce 
            }).done(function(response) {
                if (response.success) {
                    allWrappers = response.data;
                    $wrapperSelect.prop('disabled', false);
                    renderWrappersAndSelect();
                } else {
                    $wrapperSelect.empty().append(new Option('-- Error --', ''));
                    $wrapperSelect.trigger('chosen:updated');
                }
                $('#zywrap-classic-loader').hide();
            });
        });

        if ($fieldfor.length) {
            const fieldValue = $fieldfor.val();
            var targetCategory = '';
            if(fieldValue == 2) targetCategory = 'hiring_job_descriptions';
            else if(fieldValue == 3) targetCategory = 'resume_cv_coverletters_personal_branding';
            if (targetCategory !== '') $categorySelect.val(targetCategory).trigger('chosen:updated').trigger('change');
        }

        function renderWrappersAndSelect() {
            const isFeatured = $('#filter-featured-classic').attr('data-state') === '1';
            const isBase = $('#filter-base-classic').attr('data-state') === '1';
            const sortMode = $('#zywrap-classic-sort').attr('data-ordering');

            let filtered = allWrappers.filter(w => {
                if (!isFeatured && !isBase) return true;
                return (isFeatured && w.featured == 1) || (isBase && w.base == 1);
            });

            filtered.sort((a, b) => {
                if (sortMode === '0') return a.name.localeCompare(b.name);
                return (parseInt(a.ordering)||0) - (parseInt(b.ordering)||0);
            });

            $wrapperSelect.empty().append(new Option('-- Select Wrapper --', ''));
            var targetCode = '';
            filtered.forEach(w => {
                var opt = $('<option>', { value: w.code, text: w.name });
                opt.data('description', w.description);
                // Add the missing data attributes so the schema function can grab them!
                opt.data('use_case_code', w.use_case_code); 
                opt.data('ordering_number', w.ordering);
                opt.data('isfeatured', w.featured);
                opt.data('isbase', w.base);
                $wrapperSelect.append(opt);
                if (searchSelectedId && w.id == searchSelectedId) targetCode = w.code;
            });
            $('#zywrap-classic-wrapper-count').text('(' + filtered.length + ' Records)');
            if (targetCode) {
                $wrapperSelect.val(targetCode).trigger('change');
                searchSelectedId = null;
            }
            $wrapperSelect.trigger('chosen:updated');
        }

        $(document).off('change', '#zywrap-classic-wrapper').on('change', '#zywrap-classic-wrapper', function() {
            var desc = $(this).find(':selected').data('description');
            if(desc) {
                $('#zywrap-classic-description-inner').text(desc);
                $descBox.slideDown(); 
            } else {
                $descBox.slideUp();
            }
        });

        // --- 4. MODAL UI EVENTS ---
        $(document).off('click', '#zywrap-open-modal-button').on('click', '#zywrap-open-modal-button', function(e) {
            e.preventDefault();
            $modalBackdrop.show();
            $modalWrap.css('display', 'flex');
        });

        $(document).off('click', '#zywrap-classic-modal-close, #zywrap-classic-modal-backdrop').on('click', '#zywrap-classic-modal-close, #zywrap-classic-modal-backdrop', function(e) {
            e.preventDefault();
            $modalBackdrop.hide(); 
            $modalWrap.hide();
        });

        $(document).off('click', '.zywrap-chip').on('click', '.zywrap-chip', function() {
            var s = $(this).attr('data-state') === '1' ? '0' : '1';
            $(this).attr('data-state', s).toggleClass('active');
            renderWrappersAndSelect();
        });

        $(document).off('click', '#zywrap-classic-sort').on('click', '#zywrap-classic-sort', function() {
            var current = $(this).attr('data-ordering');
            var next = current === '1' ? '0' : '1';
            $(this).attr('data-ordering', next);
            $('#zywrap-sort-text').text(next === '1' ? 'Default' : 'A-Z');
            renderWrappersAndSelect();
        });

        // --- 5. EXECUTE ---
        $(document).off('click', '#zywrap-classic-run').on('click', '#zywrap-classic-run', function() {
            var btn = $(this);
            var wrapperCode = $wrapperSelect.val();
            if (!wrapperCode) return alert('Please select a wrapper first.');

            btn.prop('disabled', true).html('<span class="dashicons dashicons-update" style="animation: spin 2s infinite linear;"></span> Generating...');
            $('#zywrap-classic-spinner').addClass('is-active'); 
            $responseArea.val('Generating content, please wait...').css('opacity', '0.6');

            var overrides = {};
            $('.zywrap-classic-override-select').each(function() {
                var val = $(this).val();
                if(val) overrides[$(this).attr('data-original-id')] = val;
            });
            var schemaData = {}; 
            var structuredTextParts = [];
            $('.zywrap-classic-schema-input').each(function() {
                var val = $(this).val().trim();
                if (val !== '') {
                    var key = $(this).data('key');
                    schemaData[key] = val;
                    structuredTextParts.push(key + ': ' + val);
                }
            });

            var structuredText = structuredTextParts.join('\n');
            if (finalPrompt && structuredText) {
                finalPrompt = finalPrompt + '\n\n' + structuredText;
            } else if (structuredText) {
                finalPrompt = structuredText;
            }

            $.post(ajax_url, {
                action: 'wpjobportal_ajax', wpjobportalme: 'zywrap', task: 'executeZywrapProxy', _wpnonce: execute_nonce,
                wrapperCode: wrapperCode,
                model: $modelSelect.val(),
                language: $langSelect.val(),
                prompt: $('#zywrap-classic-prompt').val(),
                context: $('#zywrap-classic-context').val(),
                seo_keywords: $('#zywrap-classic-seo').val(),
                negative_constraints: $('#zywrap-classic-negative').val(),
                schema_inputs: JSON.stringify(schemaData),
                overrides: overrides
            }).done(function(response) {
                if (response.success) {
                    var content = response.data.output.replace(/^```json\s*/, '').replace(/\s*```$/, '');
                    $responseArea.val(content).css('opacity', '1');
                    $insertBtn.show();
                } else {
                    $responseArea.val('Error: ' + (response.data.message || 'Unknown error occurred.'));
                }
            }).always(function() {
                btn.prop('disabled', false).text(run_text || 'Generate Output');
                $('#zywrap-classic-spinner').removeClass('is-active');
            });
        });

        // --- 6. INSERT ---
        // $(document).off('click', '#zywrap-classic-insert-btn').on('click', '#zywrap-classic-insert-btn', function() {
        //     var content = $responseArea.val();
        //     if(!content || content.trim() === '') return alert('The output is empty.');

        //     if (window.wp && window.wp.data && window.wp.data.dispatch('core/editor')) {
        //         const { createBlock } = window.wp.blocks;
        //         const { insertBlocks } = window.wp.data.dispatch('core/editor');
        //         const newBlock = createBlock('core/paragraph', { content: content });
        //         insertBlocks(newBlock);
        //         $modalBackdrop.hide(); $modalWrap.hide();
        //     } else if (window.send_to_editor) {
        //         var formattedContent = '<p>' + content.replace(/\n\n/g, '</p><p>').replace(/\n/g, '<br />') + '</p>';
        //         window.send_to_editor(formattedContent);
        //         $modalBackdrop.hide(); $modalWrap.hide();
        //     } else {
        //         alert('WordPress Editor not found. Copy the text manually.');
        //     }
        // });
        // Variable to store which editor was last clicked/focused
        let lastFocusedEditorId = 'content'; // Default WP ID is 'content'

        // Track focus so we know where to send the text
        $(document).on('focusin', '.wp-editor-area', function() {
            lastFocusedEditorId = $(this).attr('id');
        });

        $(document).off('click', '#zywrap-classic-insert-btn').on('click', '#zywrap-classic-insert-btn', function() {
            var content = $responseArea.val();
            if (!content || content.trim() === '') return alert('The output is empty.');

            // 1. Check for Block Editor (Gutenberg)
            if (window.wp && window.wp.data && window.wp.data.dispatch('core/editor')) {
                const { createBlock } = window.wp.blocks;
                const { insertBlocks } = window.wp.data.dispatch('core/editor');
                const paragraphs = content.split(/\n\n+/);
                const blocks = paragraphs.map(p => createBlock('core/paragraph', { content: p.replace(/\n/g, '<br />') }));
                insertBlocks(blocks);
                closeModal();
            } 
            
            // 2. Handle Multiple Classic Editors
            else if (window.tinymce) {
                // Try to get the specific editor instance that was last used
                var editor = window.tinymce.get(lastFocusedEditorId);
                
                // If that specific editor isn't found or is hidden, fall back to the active one
                if (!editor || editor.isHidden()) {
                    editor = window.tinymce.activeEditor;
                }

                if (editor && !editor.isHidden()) {
                    var formattedContent = content.replace(/\n\n/g, '</p><p>').replace(/\n/g, '<br />');
                    editor.insertContent('<p>' + formattedContent + '</p>');
                    closeModal();
                } else {
                    // 3. Fallback: Standard Textarea (if TinyMCE is in 'Text' mode)
                    var $textarea = $('#skills');
                    //alert(lastFocusedEditorId);
                    // if(lastFocusedEditorId != ''){
                    //     var $textarea = $('#' + lastFocusedEditorId);
                    // }
                    if ($textarea.length) {
                        var currentVal = $textarea.val();
                        $textarea.val(currentVal + "\n" + content);
                        closeModal();
                    } else {
                        alert(wpjpZywrapStrings.editor_not_found);
                    }
                }
            }
        });

        function closeModal() {
            if (typeof $modalBackdrop !== 'undefined') $modalBackdrop.hide();
            if (typeof $modalWrap !== 'undefined') $modalWrap.hide();
        }

        function getClassicWrapperSchema() {
            $('#zywrap-classic-loader').show();
            var wrapperSelect = $('#zywrap-classic-wrapper');
            var wrapper_use_case_code = wrapperSelect.find(':selected').data('use_case_code') || '';

            var schemaContainer = $('#zywrap-classic-schema-container');
            var promptLabel = $('#zywrap-classic-prompt-label');

            // Reset UI before processing
            schemaContainer.empty();
            promptLabel.text('Instructions / Prompt'); // Fallback string

            if (wrapper_use_case_code === '') {
                return;
            }

            $.post(ajaxurl, {
                action: 'wpjobportal_ajax',
                wpjobportalme: 'zywrap',
                task: 'getSchemaByUseCode',
                use_case_code: wrapper_use_case_code,
                // NOTE: Ensure your PHP controller passes a schema nonce to your JS via wp_localize_script!
                '_wpnonce': wpjpzywrapClassicData.schema_nonce || '' 
            }, function(response) {

                if (response.success && response.data && response.data.schema_data) {
                    try {
                        var schema = JSON.parse(response.data.schema_data);
                        if (!schema || (!schema.req && !schema.opt)) return;

                        var html = '';
                        promptLabel.text(wpjpzywrapClassicData.additional_instructions);

                        // Helper to build accordions using the Modal's native CSS classes
                        var buildSection = function(title, data, isOpen) {
                            if (!data || Object.keys(data).length === 0) return '';
                            var openAttr = isOpen ? 'open' : '';

                            // Use modal's 'zywrap-advanced-toggle' for a native-looking accordion
                            var sectionHtml = '<details class="zywrap-advanced-toggle" style="margin-bottom: 12px; width: 100%; border-color: var(--wjp-border);" ' + openAttr + '>';
                            sectionHtml += '<summary><span>' + title + '</span><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg></summary>';
                            sectionHtml += '<div class="zywrap-advanced-content" style="gap: 10px;">';

                            for (var key in data) {
                                if (data.hasOwnProperty(key)) {
                                    var def = data[key];
                                    var isPlaceholder = def.p !== undefined ? def.p : false;
                                    var defaultVal = def.d !== undefined ? def.d : '';
                                    
                                    var placeholderAttr = isPlaceholder ? ' placeholder="' + defaultVal + '"' : '';
                                    var valueAttr = (!isPlaceholder && defaultVal) ? ' value="' + defaultVal + '"' : '';
                                    
                                    var label = key.replace(/([A-Z])/g, ' $1').replace(/^./, function(str){ return str.toUpperCase(); });

                                    sectionHtml += '<div class="wpjp-zywrap-specx-single-field" >';
                                    sectionHtml += '<label class="wpjp-zywrap-label">' + label + '</label>';
                                    sectionHtml += '<input type="text" class="zywrap-classic-schema-input" data-key="' + key + '"' + placeholderAttr + valueAttr + '>';
                                    sectionHtml += '</div>';
                                }
                            }
                            sectionHtml += '</div></details>';
                            return sectionHtml;
                        };

                        // Core Inputs open by default, Additional closed
                        html += buildSection(wpjpzywrapClassicData.core_inputs, schema.req, true);
                        html += buildSection(wpjpzywrapClassicData.additional_context, schema.opt, true);

                        schemaContainer.html(html);
                    } catch(e) {
                        console.error('Error parsing schema JSON:', e);
                    }
                }
                $('#zywrap-classic-loader').hide();
            });
        }

        // Trigger schema load on wrapper change
        $(document).on('change', '#zywrap-classic-wrapper', function() {
            if ($(this).val() !== '') {
                getClassicWrapperSchema();
            }
        });


    });
    
})(jQuery);