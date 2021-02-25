Hi {{$user->name}},
<br><br>
Exported CRA's Details:
<br><br>
@foreach($craData as $k1 => $formatcra)
	<b>{{$formatcra['code']}}</b> – Nombre de jours: {{$formatcra['days']}}
	<br>
	@foreach($formatcra['cras'] as $key => $cra)
		Du: {{convertDate($cra['start'], $date_formatText)}} au: {{convertDate($cra['end'], $date_formatText)}} - Nombre de jours : {{$cra['days']}}
		<br>
	@endforeach
	<br>
@endforeach