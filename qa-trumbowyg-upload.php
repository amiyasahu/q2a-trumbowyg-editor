<?php

/**
 * Class for handeling image upload for the editor module 
 *
 * @since 1.0
 */
class qa_trumbowyg_upload
{

    /**
     * Returns the URL for serving the request 
     * @param  string $request 
     * @return true | false 
     *
     * @since 1.0
     */
    public function match_request($request)
    {
        return ($request == 'trumbowyg-editor-upload');
    }

    /**
     * process the request for a image upload 
     * @param  string $request request for processing 
     * @return null
     * 
     * @since 1.0
     */
    public function process_request($request)
    {
        $message = '';
        $url = '';
        $success = false;

        if (is_array($_FILES) && count($_FILES)) {
            
            if (qa_opt('trumbowyg_editor_upload_images')){

                require_once QA_INCLUDE_DIR.'app/upload.php';

                $upload = qa_upload_file_one(
                    qa_opt('trumbowyg_editor_upload_max_size'),
                    qa_get('qa_only_image'),
                    qa_get('qa_only_image') ? 600 : null, // max width if it's an image upload
                    null // no max height
                );

                $message = @$upload['error'];
                // store the URL of the file uploaded 
                $url = @$upload['bloburl'];
                // if no errors returned then it is considered as a successful upload 
                $success = empty($upload['error']) ? true : false; 

            } else {
                $message = qa_lang('users/no_permission');                
            }

        }

        $data = array(
            'success' => $success,
            'file'    => qa_js($url),
            'message' => qa_js($message)
        );
        
        echo json_encode($data);

        return null;
    }
}
