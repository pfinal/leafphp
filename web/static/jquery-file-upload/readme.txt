
PHP

public function doUpload()
{
    //header("Access-Control-Allow-Origin:*");

    $up = new UploadedFile();
    if ($up->doUpload()) {

        $result = array(
            'status' => 1,
            'files' => $up->getFiles()
        );

    } else {

        $result = array(
            'status' => 0,
            'files' => $up->getError()
        );
    }

    return json_encode($result);
}
