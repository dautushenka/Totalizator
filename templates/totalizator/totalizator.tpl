<font style="font-size:14px; font-weight:bold">{title}</font><br/>
{desc}
<br />
<br />
[matches]
<table width="100%" cellpadding="0" cellpadding="0" >
	<tr>
    	<td><b>Дата проведения</b></td>
        [tournament]<td><b>Турнир</b></td>[/tournament]
        <td><b>Команда играющая дома</b></td>
        <td><b>Счёт</b></td>
        <td><b>Команда играющая в гостях</b></td>
        [rate]<td><b>Ставок</b></td>[/rate]
        [procent]<td><b>Процент предугадания</b></td>[/procent]
    </tr>
    [mat_row]
    <tr>
    	<td>{date}</td>
        [tournament]<td>{tournament}</td>[/tournament]
        <td>{komanda1}</td>
        <td>{points}</td>
        <td>{komanda2}</td>
        [rate]<td>{rates}</td>[/rate]
        [procent]<td>{procent}</td>[/procent]
    </tr>
    [/mat_row]
</table>
[save]<input type="submit" value="Сохранить" />[/save]

[edit]<input type="submit" value="Редактировать" name="edit" />[/edit]

[/matches]
[rates]
[user_page]
<div>
<a href='{matche_link}' />По матчам</a>&nbsp;|&nbsp;<a href="{tor_link}">По турнирам</a>
</div>
[/user_page]
<table width="100%" cellpadding="0" cellspacing="0" >
<tr>
	[user]<td><b>Пользователь</b></td>[/user]
    [rate]<td><b>Счёт[user_page]/Турнир[/user_page]</b></td>[/rate]
    <td><b>Очки</b></td>
</tr>
[rates_row]
<tr>
	[user]<td>{i} {user}</td>[/user]
    [rate]<td>{point}</td>[/rate]
    <td>{points}</td>
</tr>
[/rates_row]
</table>
[/rates]