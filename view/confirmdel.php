Weet je zeker dat je de volgende host wilt verwijderen? <br />
<br />
<form action="index.php?act=dodelhost" method="post">
    <input type="checkbox" name="confirm" id="confirm" value="<?= $_POST['host'] ?>"> Ja ik weet het zeker.<br />
    <input type="submit" value="Verwijder">
</form>