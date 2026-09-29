<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>Iframe content</title>
    <link rel="stylesheet" href="../assets/css/diagram-js.css">
    <link rel="stylesheet" href="../assets/css/bpmn.css">
    <link rel="stylesheet" href="../assets/css/iframe.css">
     <script src="../assets/js/myjquery.js"></script>
    <script src="../assets/js/development.js"></script>
    <script type="text/javascript" src="../assets/js/iframe.js"></script>
  </head>
  <body>
    <div id="canvas"></div>
    <form method="POST" action="view_iframe.php">
    <input type="hidden" id="bpmn_content" value="<?php echo $_GET['filename'];?>">
    </form>
  </body>
</html>
