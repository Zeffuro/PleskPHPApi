<form action="index.php?act=doaddcustomer" method="POST">
    <table>
        <tr>
            <td>Klantnummer</td>
            <td><input type="text" name="klantid" id="klantid"></td>
        </tr>
        <tr>
            <td>Voorletters</td>
            <td><input type="text" name="eig_voorletters" id="eig_voorletters"></td>
        </tr>
        <tr>
            <td>Tussenvoegsel</td>
            <td><input type="text" name="eig_tussenvoegsel" id="eig_tussenvoegsel"></td>
        </tr>
        <tr>
            <td>Achternaam</td>
            <td><input type="text" name="eig_naam" id="eig_naam"></td>
        </tr>
        <tr>
            <td>Gebruikersnaam</td>
            <td><input type="text" name="clogin" id="clogin"></td>
        </tr>
        <tr>
            <td>Wachtwoord</td>
            <td><input type="password" name="pasw" id="pasw"></td>
        </tr>
        <tr>
            <td>Wachtwoord (opnieuw)</td>
            <td><input type="password" name="pasw_confirm" id="pasw_confirm"></td>
        </tr>
        <tr>
			<td>&nbsp;</td>
			<td><input type='submit' value='Toevoegen'></td>
		</tr>
    </table>
</form>