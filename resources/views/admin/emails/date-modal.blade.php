@section('headerjs')
	<style type="text/css">
		#myModal .modal-dialog {
			width: 65%;
		}
	</style>
@stop

<div class="modal fade" id="myModal" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content"> <!-- Modal content-->
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Characters used for formating string in PHP date format</h4>
			</div>
			<div class="modal-body">
				<table class="table table-hover">
					<thead>
						<tr>
							<th><code class="parameter">format</code> character</th>
							<th>Description</th>
							<th>Example returned values</th>
						</tr>
					</thead>
					<!-- <tbody class="tbody">
						<tr>
							<td style="text-align: center;"><em class="emphasis">Day</em></td>
							<td>---</td>
							<td>---</td>
						</tr>
						<tr>
							<td><em>d</em></td>
							<td>Day of the month, 2 digits with leading zeros</td>
							<td><em>01</em> to <em>31</em></td>
						</tr>
						<tr>
							<td><em>D</em></td>
							<td>A textual representation of a day, three letters</td>
							<td><em>Mon</em> through <em>Sun</em></td>
						</tr>
						<tr>
							<td><em>j</em></td>
							<td>Day of the month without leading zeros</td>
							<td><em>1</em> to <em>31</em></td>
						</tr>
						<tr>
							<td><em>l</em> (lowercase 'L')</td>
							<td>A full textual representation of the day of the week</td>
							<td><em>Sunday</em> through <em>Saturday</em></td>
						</tr>
						<tr>
							<td><em>N</em></td>
							<td>ISO-8601 numeric representation of the day of the week</td>
							<td><em>1</em> (for Monday) through <em>7</em> (for Sunday)</td>
						</tr>
						<tr>
							<td><em>S</em></td>
							<td>English ordinal suffix for the day of the month, 2 characters</td>
							<td>
								<em>st</em>, <em>nd</em>, <em>rd</em> or
								<em>th</em>. Works well with <em>j</em>
							</td>
						</tr>
						<tr>
							<td><em>w</em></td>
							<td>Numeric representation of the day of the week</td>
							<td><em>0</em> (for Sunday) through <em>6</em> (for Saturday)</td>
						</tr>
						<tr>
							<td><em>z</em></td>
							<td>The day of the year (starting from 0)</td>
							<td><em>0</em> through <em>365</em></td>
						</tr>
						<tr>
							<td style="text-align: center;"><em class="emphasis">Week</em></td>
							<td>---</td>
							<td>---</td>
						</tr>
						<tr>
							<td><em>W</em></td>
							<td>ISO-8601 week number of year, weeks starting on Monday</td>
							<td>Example: <em>42</em> (the 42nd week in the year)</td>
						</tr>
						<tr>
							<td style="text-align: center;"><em class="emphasis">Month</em></td>
							<td>---</td>
							<td>---</td>
						</tr>
						<tr>
							<td><em>F</em></td>
							<td>A full textual representation of a month, such as January or March</td>
							<td><em>January</em> through <em>December</em></td>
						</tr>
						<tr>
							<td><em>m</em></td>
							<td>Numeric representation of a month, with leading zeros</td>
							<td><em>01</em> through <em>12</em></td>
						</tr>
						<tr>
							<td><em>M</em></td>
							<td>A short textual representation of a month, three letters</td>
							<td><em>Jan</em> through <em>Dec</em></td>
						</tr>
						<tr>
							<td><em>n</em></td>
							<td>Numeric representation of a month, without leading zeros</td>
							<td><em>1</em> through <em>12</em></td>
						</tr>
						<tr>
							<td><em>t</em></td>
							<td>Number of days in the given month</td>
							<td><em>28</em> through <em>31</em></td>
						</tr>
						<tr>
							<td style="text-align: center;"><em class="emphasis">Year</em></td>
							<td>---</td>
							<td>---</td>
						</tr>
						<tr>
							<td><em>L</em></td>
							<td>Whether it's a leap year</td>
							<td><em>1</em> if it is a leap year, <em>0</em> otherwise.</td>
						</tr>
						<tr>
							<td><em>o</em></td>
							<td>
								ISO-8601 week-numbering year. This has the same value as
								<em>Y</em>, except that if the ISO week number
								(<em>W</em>) belongs to the previous or next year, that year is used instead.
							</td>
							<td>Examples: <em>1999</em> or <em>2003</em></td>
						</tr>
						<tr>
							<td><em>Y</em></td>
							<td>A full numeric representation of a year, 4 digits</td>
							<td>Examples: <em>1999</em> or <em>2003</em></td>
						</tr>
						<tr>
							<td><em>y</em></td>
							<td>A two digit representation of a year</td>
							<td>Examples: <em>99</em> or <em>03</em></td>
						</tr>
						<tr>
							<td style="text-align: center;"><em class="emphasis">Time</em></td>
							<td>---</td>
							<td>---</td>
						</tr>
						<tr>
							<td><em>a</em></td>
							<td>Lowercase Ante meridiem and Post meridiem</td>
							<td><em>am</em> or <em>pm</em></td>
						</tr>
						<tr>
							<td><em>A</em></td>
							<td>Uppercase Ante meridiem and Post meridiem</td>
							<td><em>AM</em> or <em>PM</em></td>
						</tr>
						<tr>
							<td><em>B</em></td>
							<td>Swatch Internet time</td>
							<td><em>000</em> through <em>999</em></td>
						</tr>
						<tr>
							<td><em>g</em></td>
							<td>12-hour format of an hour without leading zeros</td>
							<td><em>1</em> through <em>12</em></td>
						</tr>
						<tr>
							<td><em>G</em></td>
							<td>24-hour format of an hour without leading zeros</td>
							<td><em>0</em> through <em>23</em></td>
						</tr>
						<tr>
							<td><em>h</em></td>
							<td>12-hour format of an hour with leading zeros</td>
							<td><em>01</em> through <em>12</em></td>
						</tr>
						<tr>
							<td><em>H</em></td>
							<td>24-hour format of an hour with leading zeros</td>
							<td><em>00</em> through <em>23</em></td>
						</tr>
						<tr>
							<td><em>i</em></td>
							<td>Minutes with leading zeros</td>
							<td><em>00</em> to <em>59</em></td>
						</tr>
						<tr>
							<td><em>s</em></td>
							<td>Seconds, with leading zeros</td>
							<td><em>00</em> through <em>59</em></td>
						</tr>
						<tr>
							<td><em>u</em></td>
							<td>
								Note that <span class="function"><strong>date()</strong></span> will always generate
								<em>000000</em> since it takes an <span class="type">integer</span>
								parameter, whereas <span class="methodname">DateTime::format()</span> does
								support microseconds if DateTime was
								created with microseconds.
							</td>
							<td>Example: <em>654321</em></td>
						</tr>
						<tr>
							<td><em>v</em></td>
							<td>Same note applies as for <em>u</em>.</td>
							<td>Example: <em>654</em></td>
						</tr>
						<tr>
							<td style="text-align: center;"><em class="emphasis">Timezone</em></td>
							<td>---</td>
							<td>---</td>
						</tr>
						<tr>
							<td><em>e</em></td>
							<td>Timezone identifier</td>
							<td>Examples: <em>UTC</em>, <em>GMT</em>, <em>Atlantic/Azores</em></td>
						</tr>
						<tr>
							<td><em>I</em> (capital i)</td>
							<td>Whether or not the date is in daylight saving time</td>
							<td><em>1</em> if Daylight Saving Time, <em>0</em> otherwise.</td>
						</tr>
						<tr>
							<td><em>O</em></td>
							<td>Difference to Greenwich time (GMT) in hours</td>
							<td>Example: <em>+0200</em></td>
						</tr>
						<tr>
							<td><em>P</em></td>
							<td>Difference to Greenwich time (GMT) with colon between hours and minutes</td>
							<td>Example: <em>+02:00</em></td>
						</tr>
						<tr>
							<td><em>T</em></td>
							<td>Timezone abbreviation</td>
							<td>Examples: <em>EST</em>, <em>MDT</em> ...</td>
						</tr>
						<tr>
							<td><em>Z</em></td>
							<td>Timezone offset in seconds. The offset for timezones west of UTC is always
							negative, and for those east of UTC is always positive.</td>
							<td><em>-43200</em> through <em>50400</em></td>
						</tr>
						<tr>
							<td style="text-align: center;"><em class="emphasis">Full Date/Time</em></td>
							<td>---</td>
							<td>---</td>
						</tr>
						<tr>
							<td><em>c</em></td>
							<td>ISO 8601 date</td>
							<td>2004-02-12T15:19:21+00:00</td>
						</tr>
						<tr>
							<td><em>r</em></td>
							<td><a href="http://www.faqs.org/rfcs/rfc2822" class="link external">»&nbsp;RFC 2822</a> formatted date</td>
							<td>Example: <em>Thu, 21 Dec 2000 16:01:07 +0200</em></td>
						</tr>
						<tr>
							<td><em>U</em></td>
							<td>Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)</td>
							<td>See also <span class="function">time()</span></td>
						</tr>
						<tr>
							<td style="text-align: center;"><em class="emphasis">Example</em></td>
							<td>---</td>
							<td>---</td>
						</tr>
						<tr>
							<td>d.m.y</td>
							<td>---</td>
							<td>07.02.12</td>
						</tr>
						<tr>
							<td>l j F Y</td>
							<td>---</td>
							<td>mardi 7 février 2012</td>
						</tr>
						<tr>
							<td>M d</td>
							<td>---</td>
							<td>mar. 07</td>
						</tr>
					</tbody> -->
					<tbody class="tbody">
						<tr>
							<td style="text-align: center;"><em class="emphasis">Day</em></td>
							<td>---</td>
							<td>---</td>
						</tr>
						<tr>
							<td><em>%a</em></td>
							<td>An abbreviated textual representation of the day</td>
							<td><em>Sun</em> through <em>Sat</em></td>
						</tr>
						<tr>
							<td><em>%A</em></td>
							<td>A full textual representation of the day</td>
							<td><em>Sunday</em> through <em>Saturday</em></td>
						</tr>
						<tr>
							<td><em>%d</em></td>
							<td>Two-digit day of the month (with leading zeros)</td>
							<td><em>01</em> to <em>31</em></td>
						</tr>
						<tr>
							<td><em>%e</em></td>
							<td>
							Day of the month, with a space preceding single digits. Not 
							implemented as described on Windows. See below for more information.
							</td>
							<td><em> 1</em> to <em>31</em></td>
						</tr>
						<tr>
							<td><em>%j</em></td>
							<td>Day of the year, 3 digits with leading zeros</td>
							<td><em>001</em> to <em>366</em></td>
						</tr>
						<tr>
							<td><em>%u</em></td>
							<td>ISO-8601 numeric representation of the day of the week</td>
							<td><em>1</em> (for Monday) through <em>7</em> (for Sunday)</td>
						</tr>
						<tr>
							<td><em>%w</em></td>
							<td>Numeric representation of the day of the week</td>
							<td><em>0</em> (for Sunday) through <em>6</em> (for Saturday)</td>
						</tr>
						<tr>
							<td style="text-align: center;"><em class="emphasis">Week</em></td>
							<td>---</td>
							<td>---</td>
						</tr>
						<tr>
							<td><em>%U</em></td>
							<td>Week number of the given year, starting with the first
							Sunday as the first week</td>
							<td><em>13</em> (for the 13th full week of the year)</td>
						</tr>
						<tr>
							<td><em>%V</em></td>
							<td>ISO-8601:1988 week number of the given year, starting with
							the first week of the year with at least 4 weekdays, with Monday
							being the start of the week</td>
							<td><em>01</em> through <em>53</em> (where 53
							accounts for an overlapping week)</td>
						</tr>
						<tr>
							<td><em>%W</em></td>
							<td>A numeric representation of the week of the year, starting
							with the first Monday as the first week</td>
							<td><em>46</em> (for the 46th week of the year beginning
							with a Monday)</td>
						</tr>
						<tr>
							<td style="text-align: center;"><em class="emphasis">Month</em></td>
							<td>---</td>
							<td>---</td>
						</tr>
						<tr>
							<td><em>%b</em></td>
							<td>Abbreviated month name, based on the locale</td>
							<td><em>Jan</em> through <em>Dec</em></td>
						</tr>
						<tr>
							<td><em>%B</em></td>
							<td>Full month name, based on the locale</td>
							<td><em>January</em> through <em>December</em></td>
						</tr>
						<tr>
							<td><em>%h</em></td>
							<td>Abbreviated month name, based on the locale (an alias of %b)</td>
							<td><em>Jan</em> through <em>Dec</em></td>
						</tr>
						<tr>
							<td><em>%m</em></td>
							<td>Two digit representation of the month</td>
							<td><em>01</em> (for January) through <em>12</em> (for December)</td>
						</tr>
						<tr>
							<td style="text-align: center;"><em class="emphasis">Year</em></td>
							<td>---</td>
							<td>---</td>
						</tr>
						<tr>
							<td><em>%C</em></td>
							<td>Two digit representation of the century (year divided by 100, truncated to an integer)</td>
							<td><em>19</em> for the 20th Century</td>
						</tr>
						<tr>
							<td><em>%g</em></td>
							<td>Two digit representation of the year going by ISO-8601:1988 standards (see %V)</td>
							<td>Example: <em>09</em> for the week of January 6, 2009</td>
						</tr>
						<tr>
							<td><em>%G</em></td>
							<td>The full four-digit version of %g</td>
							<td>Example: <em>2008</em> for the week of January 3, 2009</td>
						</tr>
						<tr>
							<td><em>%y</em></td>
							<td>Two digit representation of the year</td>
							<td>Example: <em>09</em> for 2009, <em>79</em> for 1979</td>
						</tr>
						<tr>
							<td><em>%Y</em></td>
							<td>Four digit representation for the year</td>
							<td>Example: <em>2038</em></td>
						</tr>
						<tr>
							<td style="text-align: center;"><em class="emphasis">Time</em></td>
							<td>---</td>
							<td>---</td>
						</tr>
						<tr>
							<td><em>%H</em></td>
							<td>Two digit representation of the hour in 24-hour format</td>
							<td><em>00</em> through <em>23</em></td>
						</tr>
						<tr>
							<td><em>%k</em></td>
							<td>Hour in 24-hour format, with a space preceding single digits</td>
							<td><em> 0</em> through <em>23</em></td>
						</tr>
						<tr>
							<td><em>%I</em></td>
							<td>Two digit representation of the hour in 12-hour format</td>
							<td><em>01</em> through <em>12</em></td>
						</tr>
						<tr>
							<td><em>%l (lower-case 'L')</em></td>
							<td>Hour in 12-hour format, with a space preceding single digits</td>
							<td><em> 1</em> through <em>12</em></td>
						</tr>
						<tr>
							<td><em>%M</em></td>
							<td>Two digit representation of the minute</td>
							<td><em>00</em> through <em>59</em></td>
						</tr>
						<tr>
							<td><em>%p</em></td>
							<td>UPPER-CASE 'AM' or 'PM' based on the given time</td>
							<td>Example: <em>AM</em> for 00:31, <em>PM</em> for 22:23</td>
						</tr>
						<tr>
							<td><em>%P</em></td>
							<td>lower-case 'am' or 'pm' based on the given time</td>
							<td>Example: <em>am</em> for 00:31, <em>pm</em> for 22:23</td>
						</tr>
						<tr>
							<td><em>%r</em></td>
							<td>Same as "%I:%M:%S %p"</td>
							<td>Example: <em>09:34:17 PM</em> for 21:34:17</td>
						</tr>
						<tr>
							<td><em>%R</em></td>
							<td>Same as "%H:%M"</td>
							<td>Example: <em>00:35</em> for 12:35 AM, <em>16:44</em> for 4:44 PM</td>
						</tr>
						<tr>
							<td><em>%S</em></td>
							<td>Two digit representation of the second</td>
							<td><em>00</em> through <em>59</em></td>
						</tr>
						<tr>
							<td><em>%T</em></td>
							<td>Same as "%H:%M:%S"</td>
							<td>Example: <em>21:34:17</em> for 09:34:17 PM</td>
						</tr>
						<tr>
							<td><em>%X</em></td>
							<td>Preferred time representation based on locale, without the date</td>
							<td>Example: <em>03:59:16</em> or <em>15:59:16</em></td>
						</tr>
						<tr>
							<td><em>%z</em></td>
							<td>The time zone offset. Not implemented as described on
							Windows. See below for more information.</td>
							<td>Example: <em>-0500</em> for US Eastern Time</td>
						</tr>
						<tr>
							<td><em>%Z</em></td>
							<td>The time zone abbreviation. Not implemented as described on
							Windows. See below for more information.</td>
							<td>Example: <em>EST</em> for Eastern Time</td>
						</tr>
						<tr>
							<td style="text-align: center;"><em class="emphasis">Time and Date Stamps</em></td>
							<td>---</td>
							<td>---</td>
						</tr>
						<tr>
							<td><em>%c</em></td>
							<td>Preferred date and time stamp based on locale</td>
							<td>Example: <em>Tue Feb 5 00:45:10 2009</em> for
							February 5, 2009 at 12:45:10 AM</td>
						</tr>
						<tr>
							<td><em>%D</em></td>
							<td>Same as "%m/%d/%y"</td>
							<td>Example: <em>02/05/09</em> for February 5, 2009</td>
						</tr>
						<tr>
							<td><em>%F</em></td>
							<td>Same as "%Y-%m-%d" (commonly used in database datestamps)</td>
							<td>Example: <em>2009-02-05</em> for February 5, 2009</td>
						</tr>
						<tr>
							<td><em>%s</em></td>
							<td>Unix Epoch Time timestamp (same as the <span class="function"><a href="function.time.php" class="function">time()</a></span>
							function)</td>
							<td>Example: <em>305815200</em> for September 10, 1979 08:40:00 AM</td>
						</tr>
						<tr>
							<td><em>%x</em></td>
							<td>Preferred date representation based on locale, without the time</td>
							<td>Example: <em>02/05/09</em> for February 5, 2009</td>
						</tr>
						<tr>
							<td style="text-align: center;"><em class="emphasis">Miscellaneous</em></td>
							<td>---</td>
							<td>---</td>
						</tr>
						<tr>
							<td><em>%n</em></td>
							<td>A newline character ("\n")</td>
							<td>---</td>
							</tr>
						<tr>
							<td><em>%t</em></td>
							<td>A Tab character ("\t")</td>
							<td>---</td>
						</tr>
						<tr>
							<td><em>%%</em></td>
							<td>A literal percentage character ("%")</td>
							<td>---</td>
						</tr>
						<tr>
							<td style="text-align: center;"><em class="emphasis">Example</em></td>
							<td>---</td>
							<td>---</td>
						</tr>
						<tr>
							<td>%d.%m.%y</td>
							<td>---</td>
							<td>07.02.12</td>
						</tr>
						<tr>
							<td>%A %e %B %Y</td>
							<td>---</td>
							<td>mardi 7 février 2012</td>
						</tr>
						<tr>
							<td>%b %d</td>
							<td>---</td>
							<td>mar. 07</td>
						</tr>
						<tr>
							<td>%Y-%m-%d %H:%M:%S</td>
							<td>---</td>
							<td>2018-08-20 11:34:27</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

@section('footerjs')
	<script>
		$(document).ready(function(){
			$("[data-toggle=popover]").each(function(i, obj){
				$(this).popover({
					html: true,
					content: function(){
						var id = $(this).attr('id');
						return $('#popover-content-' + id).html();
					}
				});
			});
		});
	</script>
@stop