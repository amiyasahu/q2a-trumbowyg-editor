<?php

/**
 * Class for the editor module 
 *
 * @since 1.0
 */
class qa_trumbowyg_editor
{
    /**
     * plugin URL root
     * 
     * @var string
     * 
     * @since 1.0
     */
    private $urltoroot;

    /**
     * plugin directory 
     * 
     * @var string
     * 
     * @since 1.0
     */
    private $directory;

    /**
     * included trumbowyg editor version 
     * 
     * @var string
     * 
     * @since 1.0
     */
    private $editorVersion = "v2.4.2";
    
    /**
     * base path of the trumbowyg editor relative to the plugin directory
     * 
     * @var string
     * 
     * @since 1.0
     */
    private $base_path = 'trumbowyg/dist';

    /**
     * MB in bytes 
     * 
     * @var integer
     */
    private $mb_in_bytes = 1048576;

    /**
     * Loads the module 
     * 
     * @param  string $directory
     * @param  string $urltoroot
     * @return null
     * 
     * @since 1.0
     */
    public function load_module($directory, $urltoroot) {
        $this->directory = $directory;
        $this->urltoroot = $urltoroot;
    }

    /**
     * Method for setting default options. 
     * This gets called for the first time when the options are not initialized in the database table
     * 
     * @param  string $option option key 
     * @return string the default value for the $option key 
     * 
     * @since 1.0
     */
    public function option_default($option) {
        if ($option == 'trumbowyg_editor_upload_max_size') {
            require_once QA_INCLUDE_DIR.'app/upload.php';

            return min(qa_get_max_upload_size(), $this->mb_in_bytes);
        }
    }

    /**
     * Creates a admin form in the admin/plugins page. 
     * This helps in modifying some behaviour of module 
     * 
     * @param  array &$qa_content
     * @return array
     * 
     * @since 1.0
     */
    public function admin_form(&$qa_content) {

        require_once QA_INCLUDE_DIR.'app/upload.php';

        $saved = false;

        if (qa_clicked('trumbowyg_editor_save_button')) {
            qa_opt('trumbowyg_editor_upload_images', (int)qa_post_text('trumbowyg_editor_upload_images_field'));
            qa_opt('trumbowyg_editor_upload_max_size', min(qa_get_max_upload_size(), $this->mb_in_bytes * (float)qa_post_text('trumbowyg_editor_upload_max_size_field')));
            $saved = true;
        }

        qa_set_display_rules($qa_content, array(
            'trumbowyg_editor_upload_max_size_display' => 'trumbowyg_editor_upload_images_field',
        ));

        return array(
            'ok' => $saved ? 'trumbowyg editor settings saved' : null,

            'fields' => array(
                array(
                    'label' => 'Allow images to be uploaded',
                    'type' => 'checkbox',
                    'value' => (int)qa_opt('trumbowyg_editor_upload_images'),
                    'tags' => 'name="trumbowyg_editor_upload_images_field" id="trumbowyg_editor_upload_images_field"',
                ),

                array(
                    'id' => 'trumbowyg_editor_upload_max_size_display',
                    'label' => 'Maximum size of uploads:',
                    'suffix' => 'MB (max '.$this->bytes_to_mega_html(qa_get_max_upload_size()).')',
                    'type' => 'number',
                    'value' => $this->bytes_to_mega_html(qa_opt('trumbowyg_editor_upload_max_size')),
                    'tags' => 'name="trumbowyg_editor_upload_max_size_field"',
                ),
            ),

            'buttons' => array(
                array(
                    'label' => 'Save Changes',
                    'tags' => 'name="trumbowyg_editor_save_button"',
                ),
            ),
        );
    }

    /**
     * Returns the quality of the content and format. If the format is html it is treated hightest
     *
     * @param  string $content
     * @param  string $format  
     * @return float
     * 
     * @since 1.0
     */
    public function calc_quality($content, $format)
    {
        if ($format == 'html')
            return 1.0;
        elseif ($format == '')
            return 0.8;
        else
            return 0;
    }

    /**
     * Loads the field with trumbowyg editor 
     * 
     * @param  &$qa_content 
     * @param  $content 
     * @param  $format 
     * @param  $fieldname 
     * @param  $rows
     * @return array the field for the editor 
     * 
     * @since 1.0
     */
    public function get_field(&$qa_content, $content, $format, $fieldname, $rows) {
        
        $baseUrl = $this->urltoroot . $this->base_path;

        $scriptsrc = $baseUrl . '/trumbowyg.min.js?' . $this->editorVersion;
        $upload_plugin = $baseUrl . '/trumbowyg.min.js?' . $this->editorVersion;
        $css_src = $baseUrl . '/ui/trumbowyg.min.css?' . $this->editorVersion;
        
        // TODO : Cleanup and make it configurable 
        $plugins = array('base64', 'cleanpaste', 'colors', 'insertaudio', 'noembed', 'pasteimage', 'preformatted', 'upload');

        $alreadyadded = false;

        if (isset($qa_content['script_src'])) {
            foreach ($qa_content['script_src'] as $testscriptsrc) {
                if ($testscriptsrc == $scriptsrc){
                    $alreadyadded = true;
                }
            }
        }

        if (!$alreadyadded) {

            $lang = qa_opt('site_language');
            $uploadimages = qa_opt('trumbowyg_editor_upload_images');
            $uploadall = $uploadimages && qa_opt('trumbowyg_editor_upload_all');
            $imageUploadUrl = qa_js( qa_path('trumbowyg-editor-upload', array('qa_only_image' => true)) );

            $qa_content['script_src'][] = $scriptsrc;
            $qa_content['css_src'][] = $css_src ;
            
            if(!empty($lang)){
                $qa_content['script_src'][] = $this->urltoroot . $this->base_path . '/langs/' . $lang.'.min.js?'.$this->editorVersion;
            }
            
            foreach ($plugins as $plugin) {
                $qa_content['script_src'][] = $this->urltoroot . $this->base_path . "/plugins/" . $plugin . "/trumbowyg." . $plugin . ".min.js";
            }

            $qa_content['script_lines'][] = array(
                "var updatedTrumbowygConfigs = {",
                "   btnsDef: {",
                "       image: {",
                "           dropdown: ['insertImage', 'upload', 'base64', 'noembed'],",
                "           ico: 'insertImage'",
                "       }",
                "   },",
                "   btns: [",
                "       ['viewHTML'],",
                "       ['undo', 'redo'],",
                "       ['formatting'],",
                "       'btnGrp-design',",
                "       ['link'],",
                "       ['image'],",
                "       'btnGrp-justify',",
                "       'btnGrp-lists',",
                "       ['foreColor', 'backColor'],",
                "       ['preformatted'],",
                "       ['horizontalRule'],",
                "       ['fullscreen']",
                "   ],",
                "   plugins: {",
                "       upload: {",
                "           serverPath: " . $imageUploadUrl,
                "       }",
                "   },",
                "   lang: " . qa_js($lang) . ",",
                "   svgPath: " . qa_js($this->urltoroot . $this->base_path . '/ui/icons.svg'),
                "};
                ",
            );
        }

        if ($format == 'html') {
            $html = $content;
            $text = $this->html_to_text($content);
        } else {
            $text = $content;
            $html = qa_html($content, true);
        }

        $html_prefix = '<input name="' . $fieldname . '_trumbowygeditor_ok" id="' . $fieldname . '_trumbowygeditor_ok" type="hidden" value="0">
                        <input name="' . $fieldname . '_trumbowygeditor_data" id="' . $fieldname . '_trumbowygeditor_data" type="hidden" value="'.qa_html($html).'">';

        return array(
            'tags' => 'name="' . $fieldname . '"',
            'value' => qa_html($text),
            'rows' => $rows,
            'html_prefix' => $html_prefix,
        );
    }

    /**
     * JS function to be triggered when the editor is revealed 
     * 
     * @param  $fieldname
     * @return string 
     * 
     * @since 1.0 
     */
    function load_script($fieldname) {
        return "if (window.qa_trumbowygeditorInstance_" . $fieldname . " = $('textarea[name=\'" . $fieldname . "\']').trumbowyg(updatedTrumbowygConfigs)) { " .
                    "window.qa_trumbowygeditorInstance_" . $fieldname . ".trumbowyg('html', document.getElementById(" . qa_js($fieldname . '_trumbowygeditor_data') . ").value); " .
                    "document.getElementById(" . qa_js($fieldname . '_trumbowygeditor_ok') . ").value = 1; " .
                "}";
    }

    /**
     * Code to be executed to focus the editor 
     * 
     * @param  $fieldname
     * @return string
     * 
     * @since 1.0 
     */
    function focus_script($fieldname) {
        return "window.qa_trumbowygeditorInstance_".$fieldname.".focus()";
    }
    
    /**
     * Return the html text from trumbowygeditor-iframe and push in to textarea 
     * 
     * @param  $fieldname 
     * @return string 
     * 
     * @since 1.0 
     */
    function update_script($fieldname) {
        return "window.qa_trumbowygeditorInstance_".$fieldname.".val(window.qa_trumbowygeditorInstance_".$fieldname.".trumbowyg('html'))";
    }

    /**
     * Reads the post from the $POST variable and retrn the content 
     * 
     * @param  $fieldname
     * @return string
     * 
     * @since 1.0 
     */
    public function read_post($fieldname) {

        if (qa_post_text($fieldname . '_trumbowygeditor_ok')) {
            // trumbowyg was loaded successfully
            $html = qa_post_text($fieldname);

            // remove <p>, <br>, etc... since those are OK in text
            $htmlformatting = preg_replace('/<\s*\/?\s*(br|p)\s*\/?\s*>/i', '', $html);

            if (preg_match('/<.+>/', $htmlformatting)) {
                // if still some other tags, it's worth keeping in HTML
                // qa_sanitize_html() is ESSENTIAL for security
                return array(
                    'format' => 'html',
                    'content' => qa_sanitize_html($html, false, true),
                );
            } else {
                // convert to text
                $viewer = qa_load_module('viewer', '');

                return array(
                    'format' => '',
                    'content' => $this->html_to_text($html),
                );
            }
        } else {
            // trumbowyg was not loaded so treat it as plain text
            return array(
                'format' => '',
                'content' => qa_post_text($fieldname),
            );
        }
    }

    /**
     * Converts the HTML into text format and return 
     * 
     * @param  $html
     * @return string
     * 
     * @since 1.0 
     */
    private function html_to_text($html) {
        $viewer = qa_load_module('viewer', '');
        return $viewer->get_text($html, 'html', array());
    }

    /**
     * Converts bytes to MB 
     * 
     * @param  $bytes
     * @return Megabyte representation of the $bytes 
     * 
     * @since 1.0 
     */
    private function bytes_to_mega_html($bytes) {
        return qa_html(number_format($bytes/$this->mb_in_bytes, 1));
    }
    
}
