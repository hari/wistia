<?php
define('API_KEY', 'ca53f7568dff9c273f68bf4f70d0eee8a3a3be181992100188ad2d3288b07a79');
define('API_URL', 'https://api.wistia.com/v1/medias.json?api_password=');

function saveCSV($csv) {
  header('Content-Type: text/csv');
  header(sprintf('Content-Disposition: attachment; filename="%s.csv"', 'videos-'.time().'.csv'));
  echo $csv;
  die;
}

function createCSV($medias) {
  $result = [];
  foreach ($medias as $media) {
    if ($media->type === 'Video') {
      $result[] = implode(",", [
        $media->name,
        $media->thumbnail->url,
        array_filter($media->assets, function ($entry) {
          return $entry->type === 'OriginalFile';
        })[0]->url . '.mp4'
      ]);
    }
  }
  array_unshift($result, implode(",", ['Name', 'Thumbnail', 'Video']));
  return implode("\r\n", $result);
}

function getIds() {
  $urls = '';
  if (isset($_POST['urls'])) {
    $urls = $_POST['urls'];
  }
  $urls = preg_replace('/\r?\n/i', ' ', $urls);
  return array_map(function ($url) {
    $pcs = explode("/", $url);
    return array_pop($pcs);
  },
  array_filter(explode(' ', $urls), function ($url) {
    return strlen(trim($url)) > 0 && stristr($url, '/medias/');
  }));
}

function getVideos() {
  try {
    $response = file_get_contents(API_URL . API_KEY);
    $medias = json_decode($response);
    $ids = getIds();
    $csv;
    if (count($ids) > 0) {
      $csv = createCSV(
        array_filter($medias, function ($media) use ($ids) {
          return in_array($media->hashed_id, $ids);
        })
      );
    } else {
      $csv = createCSV($medias);
    }
    saveCSV($csv);
  } catch (Exception $exp) {
    die($exp->getMessage());
  }
}
if (isset($_POST['urls'])) {
  getVideos();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Wistia CSV</title>
  <style>
    body, html {
      margin: 0;
      padding: 0;
      background-color: #fafafa;
    }
    div {
      max-width: 400px;
      margin: 16px auto;
    }
    textarea {
      padding: 4px 8px;
      width: 100%;
      box-sizing: border-box;
      border: 1px solid #aaa;
      min-height: 150px;
      margin-bottom: 8px;
    }
    button {
      width: 100%;
      padding: 8px;
      display: block;
      background-color: green;
      border: 1px solid darkgreen;
      border-radius: 4px;
      font-size: 14px;
      color: #fff;
      text-transform: uppercase;
    }
    button:hover {
      opacity: 0.8;
    }
    label {
      display: block;
      margin-bottom: 4px;
      color: #888;
      text-transform: uppercase;
      font-size: 13px;
    }
  </style>
</head>
<body>
  <div>
    <form method="POST">
      <label>Enter URLs (separated by space or new line)</label>
      <textarea
        name="urls"
        placeholder="URLs (optional)"></textarea>
      <button type="submit">Get CSV</button>
    </form>
  </div>
</body>
</html>
