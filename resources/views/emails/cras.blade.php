New CRAs Posted by {{$user->name}}.
<br><br>
{{$craMonth}}
<br><br>
CRA's Details:
<br><br>
@foreach($craData as $k1 => $formatcra)
	<b>{{$formatcra['code']}}</b> – Nombre de jours: {{$formatcra['days']}}
	<br>
	@foreach($formatcra['cras'] as $key => $cra)
		Du: {{convertDate($cra['start'], $date_formatText)}} au: {{convertDate($cra['end'], $date_formatText)}} - Nombre de jours : {{$cra['days']}}
		<br>
		la date de début est un lundi: {{isMonday($cra['start']) ? $text_yes : $text_no}}
		<br>
	@endforeach
	<br>
@endforeach
<br><br>
Absence Details:
<br><br>
@foreach($absences as $key => $formatabsence)
	<b>{{$formatabsence['reasonStr']}}</b> – Nombre de jours: {{$formatabsence['days']}}
	<br>
	@foreach($formatabsence['absences'] as $key => $absence)
		Du: {{convertDate($absence['start'], $date_formatText)}} au: {{convertDate($absence['end'], $date_formatText)}} - Nombre de jours : {{$absence['days']}}
		<br>
	@endforeach
	<br>
@endforeach