<?php
namespace vcms;

class REST {


  const GET = 0;
  const POST = 1;
  const DELETE = 2;



  /**
   * from http://stackoverflow.com/a/18678678/773595
   */
  /**
   * @return array The data as saved in the global PUT
   */
  static function parse_put_form_data () {

    global $_PUT;


    $putdata = fopen("php://input", "r");
    $raw_data = '';

    while ($chunk = fread($putdata, 1024)) {
      $raw_data .= $chunk;
    }
    fclose($putdata);

    $boundary = substr($raw_data, 0, strpos($raw_data, "\r\n"));

    // no boundaries means the data is just a line of variables
    if (empty($boundary)) {
      parse_str($raw_data, $data);
      $GLOBALS['_PUT'] = $data;
      return $data;
    }


    $parts = array_slice(explode($boundary, $raw_data), 1);
    $data = [];

    foreach ($parts as $part) {
      // If this is the last part, break
      if ($part == "--\r\n") break;

      // Separate content from headers
      $part = ltrim($part, "\r\n");
      list($raw_headers, $body) = explode("\r\n\r\n", $part, 2);

      // Parse the headers list
      $raw_headers = explode("\r\n", $raw_headers);
      $headers = array();
      foreach ($raw_headers as $header) {
        list($name, $value) = explode(':', $header);
        $headers[strtolower($name)] = ltrim($value, ' ');
      }

      // Parse the Content-Disposition to get the field name, etc.
      if (isset($headers['content-disposition'])) {
        $filename = null;
        $tmp_name = null;
        preg_match(
        '/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/',
        $headers['content-disposition'],
        $matches
        );
        list(, $type, $name) = $matches;

        //Parse File
        if (isset($matches[4])) {
          //if labeled the same as previous, skip
          if (isset($_FILES[$matches[2]])) {
            continue;
          }

          //get filename
          $filename = $matches[4];

          //get tmp name
          $filename_parts = pathinfo($filename);
          $tmp_name = tempnam(
          ini_get('upload_tmp_dir'), $filename_parts['filename']
          );

          //populate $_FILES with information, size may be off in multibyte situation
          $_FILES[$matches[2]] = array(
          'error' => 0,
          'name' => $filename,
          'tmp_name' => $tmp_name,
          'size' => strlen($body),
          'type' => $value
          );

          //place in temporary directory
          file_put_contents($tmp_name, $body);
        } //Parse Field
        else {
          $data[$name] = substr($body, 0, strlen($body) - 2);
        }
      }

    }
    $GLOBALS['_PUT'] = $data;

    return $_PUT;
  }


  public static function get_input_data (): Array {

    $input = fopen('php://input', 'r');

    $rawData = '';

    while ($chunk = fread($input, 1024)) {
      $rawData .= $chunk;
    }
    fclose($input);


    $boundary = substr($rawData, 0, strpos($rawData, "\r\n"));

    // no boundaries means the data is just a line of variables
    if (empty($boundary)) {
      parse_str($rawData, $data);
      return $data;
    }

    $parts = array_slice(explode($boundary, $rawData), 1);
    $data = [];

    foreach ($parts as $part) {
      // If this is the last part, break
      if ($part == "--\r\n") break;

      // Separate content from headers
      $part = ltrim($part, "\r\n");
      list($raw_headers, $body) = explode("\r\n\r\n", $part, 2);

      // Parse the headers list
      $raw_headers = explode("\r\n", $raw_headers);
      $headers = array();
      foreach ($raw_headers as $header) {
        list($name, $value) = explode(':', $header);
        $headers[strtolower($name)] = ltrim($value, ' ');
      }

      // Parse the Content-Disposition to get the field name, etc.
      if (isset($headers['content-disposition'])) {
        $filename = null;
        $tmp_name = null;
        preg_match(
        '/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/',
        $headers['content-disposition'],
        $matches
        );
        list(, $type, $name) = $matches;

        //Parse File
        if (isset($matches[4])) {
          //if labeled the same as previous, skip
          if (isset($_FILES[$matches[2]])) {
            continue;
          }

          //get filename
          $filename = $matches[4];

          //get tmp name
          $filename_parts = pathinfo($filename);
          $tmp_name = tempnam(
          ini_get('upload_tmp_dir'), $filename_parts['filename']
          );

          //populate $_FILES with information, size may be off in multibyte situation
          $_FILES[$matches[2]] = array(
          'error' => 0,
          'name' => $filename,
          'tmp_name' => $tmp_name,
          'size' => strlen($body),
          'type' => $value
          );

          //place in temporary directory
          file_put_contents($tmp_name, $body);
        } //Parse Field
        else {
          $data[$name] = substr($body, 0, strlen($body) - 2);
        }
      }

    }

    return $data;
  }


  /**
   * @return int|null
   */
  static function get_request_method () {
    switch ($_SERVER['REQUEST_METHOD']) {
      case 'GET': return self::GET;
      case 'POST': return self::POST;
      case 'DELETE': return self::DELETE;

      default: return -1;
    }
  }
}
