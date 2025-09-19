<!-- layout.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Music Site</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <?php include 'floating_music.php'; ?> <!-- persistent player -->

  <div id="main-content">
    <?php include 'index1.php'; ?> <!-- default page content -->
    <?php include  'artist.php' ?>
  </div>

  <script src="assets/js/fm.js"></script> <!-- music player JS -->
  <script src="assets/js/spa.js"></script> <!-- SPA navigation JS -->
</body>
</html>
