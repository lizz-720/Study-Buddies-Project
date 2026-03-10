<?php
$name = isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : "User";
?>
<div style="display:flex; gap:10px; align-items:center; margin-bottom:16px; padding:12px; border:1px solid #ddd; border-radius:10px;">
  <strong style="font-size:18px;">StudyBuddies</strong>
  <span style="margin-left:auto;">Hi, <?php echo $name; ?>!</span>

  <a href="index.php"><button type="button">Home</button></a>
  <a href="flashcards.php"><button type="button">Flashcards</button></a>
  <a href="fill_blank.php"><button type="button">Fill-Blank</button></a>
  <a href="timer.php"><button type="button">Timer</button></a>
  <a href="logout.php"><button type="button">Logout</button></a>
</div>