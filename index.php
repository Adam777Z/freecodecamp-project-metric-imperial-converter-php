<?php
$path_prefix = '';

$units = [
	'gal' => [ 'gallons', 'l', 3.78541 ],
	'l' => [ 'liters', 'gal', 0.264172 ],
	'lbs' => [ 'pounds', 'kg', 0.453592 ],
	'kg' => [ 'kilograms', 'lbs', 2.204624 ],
	'mi' => [ 'miles', 'km', 1.60934 ],
	'km' => [ 'kilometers', 'mi', 0.621373 ],
];

if ( isset( $_SERVER['PATH_INFO'] ) ) {
	$path_count = substr_count( $_SERVER['PATH_INFO'], '/' ) - 1;

	for ( $i = 0; $i < $path_count; $i++ ) {
		$path_prefix .= '../';
	}

	if ( strpos( $_SERVER['PATH_INFO'], '/api/convert' ) !== false ) {
		if ( ! empty( $_GET['input'] ) ) {
			$number = preg_replace( '/[A-Za-z\s]/', '', $_GET['input'] );

			if ( empty( $number ) && $number !== '0' ) {
				$number = 1;
			}

			if ( strpos( $number, '/' ) !== false ) {
				$number = preg_replace( '~\/+~', '/', $number );
				$numbers = explode( '/', $number );
				$number = count( $numbers ) === 2 ? floatval( $numbers[0] ) / floatval( $numbers[1] ) : 0;
			}

			$number = floatval( $number );
			$unit = strtolower( preg_replace( '/[^A-Za-z]/', '', $_GET['input'] ) );
			$target_unit = isset( $units[$unit] ) ? $units[$unit][1] : false;

			if ( $number <= 0 && ! $target_unit ) {
				header( 'Content-Type: application/json; charset=utf-8' );
				echo json_encode( [
					'error' => 'invalid number and unit',
				] );
				exit;
			}

			if ( $number <= 0 ) {
				header( 'Content-Type: application/json; charset=utf-8' );
				echo json_encode( [
					'error' => 'invalid number',
				] );
				exit;
			}

			if ( ! $target_unit ) {
				header( 'Content-Type: application/json; charset=utf-8' );
				echo json_encode( [
					'error' => 'invalid unit',
				] );
				exit;
			}

			$return_number = (float) number_format( $number * $units[$unit][2], 5 );

			header( 'Content-Type: application/json; charset=utf-8' );
			echo json_encode( [
				'initNum' => $number,
				'initUnit' => $unit,
				'returnNum' => $return_number,
				'returnUnit' => $target_unit,
				'string' => "$number {$units[$unit][0]} converts to $return_number {$units[$target_unit][0]}",
			] );
			exit;
		} else {
			header( 'Content-Type: application/json; charset=utf-8' );
			echo json_encode( [
				'error' => 'input is required',
			] );
			exit;
		}
	} elseif ( strpos( $_SERVER['PATH_INFO'], '/api/test' ) !== false ) {
		$tests = [];

		$input = '32l';
		$output = 32;
		$data = get_api_data( $input );
		$tests[] = [
			'title' => 'Whole number input',
			'input' => $input,
			'output' => $output,
			'passed' => isset( $data['initNum'] ) && $data['initNum'] === $output,
		];

		$input = '3.1l';
		$output = 3.1;
		$data = get_api_data( $input );
		$tests[] = [
			'title' => 'Decimal input',
			'input' => $input,
			'output' => $output,
			'passed' => isset( $data['initNum'] ) && $data['initNum'] === $output,
		];

		$input = '1/2l';
		$output = 0.5;
		$data = get_api_data( $input );
		$tests[] = [
			'title' => 'Fractional input',
			'input' => $input,
			'output' => $output,
			'passed' => isset( $data['initNum'] ) && $data['initNum'] === $output,
		];

		$input = '6/2.5l';
		$output = 2.4;
		$data = get_api_data( $input );
		$tests[] = [
			'title' => 'Fractional input with decimal',
			'input' => $input,
			'output' => $output,
			'passed' => isset( $data['initNum'] ) && $data['initNum'] === $output,
		];

		$input = '0/20/20l';
		$output = 'invalid number';
		$data = get_api_data( $input );
		$tests[] = [
			'title' => 'Invalid input (double fraction)',
			'input' => $input,
			'output' => $output,
			'passed' => isset( $data['error'] ) && $data['error'] === $output,
		];

		$input = 'l';
		$output = 1;
		$data = get_api_data( $input );
		$tests[] = [
			'title' => 'No numerical input (default to 1)',
			'input' => $input,
			'output' => $output,
			'passed' => isset( $data['initNum'] ) && $data['initNum'] === $output,
		];

		$input = 'i';
		$output = 'invalid unit';
		$data = get_api_data( $input );
		$tests[] = [
			'title' => 'Unknown unit input',
			'input' => $input,
			'output' => $output,
			'passed' => isset( $data['error'] ) && $data['error'] === $output,
		];

		$input = [ 'gal', 'l', 'mi', 'km', 'lbs', 'kg', 'GAL', 'L', 'MI', 'KM', 'LBS', 'KG' ];
		$output = [ 'gal', 'l', 'mi', 'km', 'lbs', 'kg', 'gal', 'l', 'mi', 'km', 'lbs', 'kg' ];
		$result = true;
		foreach ( $input as $key => $value ) {
			$data = get_api_data( $input[$key] );
			if ( isset( $data['initUnit'] ) && $data['initUnit'] !== $output[$key] ) {
				$result = false;
				break;
			}
		}
		$tests[] = [
			'title' => 'For each valid unit inputs',
			'input' => $input,
			'output' => $output,
			'passed' => $result,
		];

		$input = [ 'gal', 'l', 'mi', 'km', 'lbs', 'kg' ];
		$output = [ 'l', 'gal', 'km', 'mi', 'kg', 'lbs' ];
		$result = true;
		foreach ( $input as $key => $value ) {
			$data = get_api_data( $input[$key] );
			if ( isset( $data['returnUnit'] ) && $data['returnUnit'] !== $output[$key] ) {
				$result = false;
				break;
			}
		}
		$tests[] = [
			'title' => 'For each valid unit inputs 2',
			'input' => $input,
			'output' => $output,
			'passed' => $result,
		];

		$input = [ 'gal', 'l', 'mi', 'km', 'lbs', 'kg' ];
		$output = [ 'gallons', 'liters', 'miles', 'kilometers', 'pounds', 'kilograms' ];
		$result = true;
		foreach ( $input as $key => $value ) {
			$data = get_api_data( $input[$key] );
			if ( isset( $data['string'] ) ) {
				if ( explode( ' ', $data['string'] )[1] !== $output[$key] ) {
					$result = false;
					break;
				}
			}
		}
		$tests[] = [
			'title' => 'For each valid unit inputs 3',
			'input' => $input,
			'output' => $output,
			'passed' => $result,
		];

		$input = '5gal';
		$output = 18.92705;
		$data = get_api_data( $input );
		$tests[] = [
			'title' => 'gal to l',
			'input' => $input,
			'output' => $output,
			'passed' => isset( $data['returnNum'] ) && $data['returnNum'] === $output,
		];

		$input = '5l';
		$output = 1.32086;
		$data = get_api_data( $input );
		$tests[] = [
			'title' => 'l to gal',
			'input' => $input,
			'output' => $output,
			'passed' => isset( $data['returnNum'] ) && $data['returnNum'] === $output,
		];

		$input = '5mi';
		$output = 8.0467;
		$data = get_api_data( $input );
		$tests[] = [
			'title' => 'mi to km',
			'input' => $input,
			'output' => $output,
			'passed' => isset( $data['returnNum'] ) && $data['returnNum'] === $output,
		];

		$input = '5km';
		$output = 3.10687;
		$data = get_api_data( $input );
		$tests[] = [
			'title' => 'km to mi',
			'input' => $input,
			'output' => $output,
			'passed' => isset( $data['returnNum'] ) && $data['returnNum'] === $output,
		];

		$input = '5lbs';
		$output = 2.26796;
		$data = get_api_data( $input );
		$tests[] = [
			'title' => 'lbs to kg',
			'input' => $input,
			'output' => $output,
			'passed' => isset( $data['returnNum'] ) && $data['returnNum'] === $output,
		];

		$input = '5kg';
		$output = 11.02312;
		$data = get_api_data( $input );
		$tests[] = [
			'title' => 'kg to lbs',
			'input' => $input,
			'output' => $output,
			'passed' => isset( $data['returnNum'] ) && $data['returnNum'] === $output,
		];

		$input = '10l';
		$output = 2.64172;
		$data = get_api_data( $input );
		$tests[] = [
			'title' => 'Convert 10l (valid input)',
			'input' => $input,
			'output' => $output,
			'passed' => isset( $data['returnNum'] ) && $data['returnNum'] === $output,
		];

		$input = '32g';
		$output = 'invalid unit';
		$data = get_api_data( $input );
		$tests[] = [
			'title' => 'Convert 32g (invalid unit)',
			'input' => $input,
			'output' => $output,
			'passed' => isset( $data['error'] ) && $data['error'] === $output,
		];

		$input = '3/7.2/4kg';
		$output = 'invalid number';
		$data = get_api_data( $input );
		$tests[] = [
			'title' => 'Convert 3/7.2/4kg (invalid number)',
			'input' => $input,
			'output' => $output,
			'passed' => isset( $data['error'] ) && $data['error'] === $output,
		];

		$input = '3/7.2/4kilomegagram';
		$output = 'invalid number and unit';
		$data = get_api_data( $input );
		$tests[] = [
			'title' => 'Convert 3/7.2/4kilomegagram (invalid number and unit)',
			'input' => $input,
			'output' => $output,
			'passed' => isset( $data['error'] ) && $data['error'] === $output,
		];

		$input = 'kg';
		$output = 1;
		$data = get_api_data( $input );
		$tests[] = [
			'title' => 'Convert kg (no number)',
			'input' => $input,
			'output' => $output,
			'passed' => isset( $data['initNum'] ) && $data['initNum'] === $output,
		];

		header( 'Content-Type: application/json; charset=utf-8' );
		echo json_encode( $tests );
		exit;
	} else {
		redirect_to_index();
	}
}

function redirect_to_index() {
	global $path_prefix;

	if ( $path_prefix == '' ) {
		$path_prefix = './';
	}

	header( "Location: $path_prefix" );
	exit;
}

function get_api_data( $input ) {
	$url = 'http' . ( ! empty( $_SERVER['HTTPS'] ) ? 's' : '' ) . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

	if ( isset( $_SERVER['PATH_INFO'] ) ) {
		$url = str_replace( $_SERVER['PATH_INFO'], '', $url ) . '/';
	}

	$url .= 'api/convert?input=' . $input;

	// $ch = curl_init( $url );

	// curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

	// $result = curl_exec( $ch );

	// if ( $result ) {
	// 	$data = json_decode( $result, true );
	// } else {
	// 	$data = [];
	// }

	// $data = $result ? json_decode( $result, true ) : [];

	// curl_close( $ch );

	$result = file_get_contents( $url );
	$data = json_decode( $result, true );

	return $data;
}
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Metric-Imperial Converter</title>
	<meta name="description" content="freeCodeCamp - Information Security and Quality Assurance Project: Metric-Imperial Converter">
	<link rel="icon" type="image/x-icon" href="<?php echo $path_prefix; ?>favicon.ico">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<link rel="stylesheet" href="<?php echo $path_prefix; ?>assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="<?php echo $path_prefix; ?>assets/css/style.min.css">
	<script src="<?php echo $path_prefix; ?>assets/js/script.min.js"></script>
</head>
<body>
	<div class="container">
		<div class="p-4 my-4 bg-light rounded-3">
			<div class="row">
				<div class="col">
					<header>
						<h1 id="title" class="text-center">Metric-Imperial Converter</h1>
					</header>

					<div id="user-stories">
						<h3>User Stories:</h3>
						<ol>
							<li>I can <b>GET</b> <code>/api/convert</code> with a single parameter containing an accepted number and unit and have it converted. Hint: Split the input by looking for the index of the first character which will mark the start of the unit.</li>
							<li>I can convert 'gal' to 'l' and vice versa. <b>(1 gal to 3.78541 l)</b></li>
							<li>I can convert 'lbs' to 'kg' and vice versa. <b>(1 lbs to 0.45359 kg)</b></li>
							<li>I can convert 'mi' to 'km' and vice versa. <b>(1 mi to 1.60934 km)</b></li>
							<li>If my number is invalid, returned will be 'invalid number'.</li>
							<li>If my unit of measurement is invalid, returned will be 'invalid unit'.</li>
							<li>If both are invalid, returned will be 'invalid number and unit'.</li>
							<li>I can use fractions, decimals or both in my parameter (for example: 5, 1/2, 2.5/6), but if nothing is provided it will default to 1.</li>
							<li>Returned will consist of the initNum, initUnit, returnNum, returnUnit, and string spelling out units in format <code>{initNum} {initial_Units} converts to {returnNum} {return_Units}</code> with the result rounded to 5 decimals.</li>
							<li>All 21 <a href="<?php echo $path_prefix; ?>api/test" target="_blank">tests</a> are complete and passing.</li>
						</ol>
						<h3>Example usage:</h3>
						<ul>
							<li><code><a href="<?php echo $path_prefix; ?>api/convert?input=4gal" target="_blank">/api/convert?input=4gal</a></code></li>
							<li><code><a href="<?php echo $path_prefix; ?>api/convert?input=3.1mi" target="_blank">/api/convert?input=3.1mi</a></code></li>
							<li><code><a href="<?php echo $path_prefix; ?>api/convert?input=1/2km" target="_blank">/api/convert?input=1/2km</a></code></li>
							<li><code><a href="<?php echo $path_prefix; ?>api/convert?input=5.4/3lbs" target="_blank">/api/convert?input=5.4/3lbs</a></code></li>
							<li><code><a href="<?php echo $path_prefix; ?>api/convert?input=kg" target="_blank">/api/convert?input=kg</a></code></li>
						</ul>
						<h3>Example return:</h3>
						<p>
							<code>{"initNum":3.1,"initUnit":"mi","returnNum":4.98895,"returnUnit":"km","string":"3.1 miles converts to 4.98895 kilometers"}</code>
						</p>
					</div>

					<hr>

					<div id="test-ui">
						<h3>Front-End:</h3>
						<form id="convert-form" class="d-flex">
							<input type="text" name="input" id="convert-field" class="form-control me-2" placeholder="3.1mi">
							<input type="submit" id="convert" class="btn btn-primary" value="Convert">
						</form>
						<p id="result"></p>
						<p>
							<code id="result-json"></code>
						</p>
					</div>

					<hr>

					<div class="footer text-center">by <a href="https://www.freecodecamp.org" target="_blank">freeCodeCamp</a> (ISQA2) & <a href="https://www.freecodecamp.org/adam777" target="_blank">Adam</a> | <a href="https://github.com/Adam777Z/freecodecamp-project-metric-imperial-converter-php" target="_blank">GitHub</a></div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>