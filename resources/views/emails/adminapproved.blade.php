{{$userData->name}}'s absence request approved by {{$by}}.
<br><br>
Absence Details:
<br><br>
	Days: {{$absence->days}}
<br>
	Start Date: {{convertDate($absence->start, $date_formatText)}}
<br>
	End Data: {{convertDate($absence->end, $date_formatText)}}
<br>
	Start Half: {{$absence->startHalf ? 'OUI' : 'NON'}}
<br>
	End Half: {{$absence->endHalf ? 'OUI' : 'NON'}}
<br>
	la date de début est un lundi: {{isMonday($absence->start) ? $text_yes : $text_no}}
<br>