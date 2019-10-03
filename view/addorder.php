<form action="index.php?act=doaddorder" method="POST">
    <table>
        <tr>
            <td>Ordernummer</td>
            <td><input type="text" name="orderid" id="orderid"></td>
        </tr>
        <tr>
            <td>Domeinnaam</td>
            <td><input type="text" name="domein" id="domein"></td>
        </tr>
        <tr>
            <td>Extentie</td>
            <td>
                <select name="ext" id="ext">
    					<option value="nl">.nl</option>
    					<option value="com">.com</option>
                        <option value="eu">.eu</option>
				</select>
            </td>
        </tr>
        <tr>
            <td>Host</td>
            <td><input type="text" name="ip" id="ip" value="192.168.1.60"></td>
        </tr>
        <tr>
            <td>Pakket</td>
            <td>
                <select name="pakket" id="pakket">
    					<option value="presence">Presence (50 MB opslag, 1 GB dataverkeer)</option>
    					<option value="starters">Starters (100 MB opslag, 2 GB dataverkeer)</option>
                        <option value="business">Business (250 MB opslag, 5 GB dataverkeer)</option>
				</select>
            </td>
        </tr>
        <tr>
            <td>Klantnummer</td>
            <td><input type="text" name="klantnummer" id="klantnummer"></td>
        </tr>
        <tr>
			<td>&nbsp;</td>
			<td><input type='submit' value='Toevoegen'></td>
		</tr>
    </table>
</form>