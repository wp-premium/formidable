<?php

class FrmProMathController {

	/**
	 * Process content of frm-math shortcode and evaluate as a math expression.
	 *
	 * @param $atts
	 * @param string $content
	 *
	 * @return mixed|string
	 */
	public static function math_shortcode( $atts, $content = '' ) {

		if ( ! $content || $content === '' ) {
			return '';
		}

		$atts = shortcode_atts(
			array(
				'decimal'       => 0,
				'dec_point'     => '.',
				'thousands_sep' => ',',
				'error'         => '',
				'clean'         => 0,
			), $atts, 'frm-math' );

		$expression = self::get_math_expression_from_shortcode_content( $content, $atts );
		if ( $expression === '' ) {
			return $expression;
		}
		if ( self::expression_contains_non_math_characters( $expression ) ) {
			return self::get_error_content( $atts, $expression );
		}

		$result = self::calculate_math_in_string( $expression );
		if ( ! is_numeric( $result ) ) {
			return self::get_error_content( $atts, $expression );
		}

		return number_format( $result, $atts['decimal'], $atts['dec_point'], $atts['thousands_sep'] );
	}

	/**
	 * Process frm-math shortcode content into string with appropriate content for math expression.
	 *
	 * @param $content
	 * @param $atts
	 *
	 * @return null|string|string[]
	 */
	private static function get_math_expression_from_shortcode_content( $content, $atts ) {
		$expression = do_shortcode( $content );
		$expression = preg_replace( '/&#8211;/', '-', $expression );
		$expression = self::clear_expression_of_extra_characters( $expression, $atts );

		return $expression;
	}

	/**
	 * Remove non-math characters or extra spaces, depending on the value of the clean attribute
	 *
	 * @param $expression
	 * @param $atts
	 *
	 * @return null|string|string[]
	 */
	private static function clear_expression_of_extra_characters( $expression, $atts ) {
		if ( isset( $atts['clean'] ) && $atts['clean'] ) {
			return preg_replace( '/[^\+\-\/\*0-9\.\(\)\%]/', '', $expression );
		}

		return preg_replace( '/[\s\,]/', '', $expression );
	}

	/**
	 * Tests if an expression contains characters that don't belong in a math expression, e.g. letters
	 *
	 * @param $expression
	 *
	 * @return bool
	 */
	private static function expression_contains_non_math_characters( $expression ) {
		$result = preg_match( '/[^\+\-\/\*0-9\.\s\(\)\%]/', $expression );

		return ( $result === 1 );
	}

	/**
	 * Returns appropriate error content.
	 *
	 * @param $atts
	 * @param $expression
	 *
	 * @return string
	 */
	private static function get_error_content( $atts, $expression ) {
		if ( ! isset( $atts['error'] ) ) {
			return '';
		}

		if ( $atts['error'] === 'debug' ) {
			return $expression;
		}

		return ( $atts['error'] );
	}

	/**
	 * Calculate value of string math expression.
	 *
	 * @param $math_string
	 *
	 * @return array|array[]|false|mixed|string|string[]
	 */
	private static function calculate_math_in_string( $math_string ) {
		$math_array = self::parse_math_string_into_array( $math_string );
		if ( ! is_array( $math_array ) ) {
			return $math_array;
		}
		$post_fix = self::convert_to_postfix( $math_array );
		if ( ! is_array( $post_fix ) ) {
			return $post_fix;
		}

		return self::evaluate_postfix_expression( $post_fix );
	}

	/**
	 * Convert math expression string into array.
	 *
	 * @param $math_string
	 *
	 * @return array|array[]|false|string|string[]
	 */
	private static function parse_math_string_into_array( $math_string ) {
		$math_array = preg_split( '/([\+\-\*\/\(\)\%])/', $math_string, - 1, PREG_SPLIT_DELIM_CAPTURE );
		$math_array = array_filter( $math_array, 'strlen' );
		$math_array = array_values( $math_array );
		$math_array = self::set_negative_numbers( $math_array );

		return $math_array;
	}

	/**
	 * Convert numbers in math array to negative numbers, where appropriate.
	 *
	 * @param $math_array
	 *
	 * @return array|string
	 */
	private static function set_negative_numbers( $math_array ) {
		$negatives = preg_grep( '/\-/', $math_array );

		if ( count( $negatives ) === 0 ) {
			return $math_array;
		}

		foreach ( $negatives as $key => $negative ) {
			if ( $key === 0 || ( preg_match( '/[\-\+\*\/\(\%]/', $math_array[ $key - 1 ] ) > 0 ) ) {
				$next = $key + 1;
				if ( isset( $math_array[ $next ] ) && is_numeric( $math_array[ $next ] ) ) {
					$math_array[ $next ] = - 1 * $math_array[ $next ];
					unset( $math_array[ $key ] );
				}
			}
		}

		return array_values( $math_array );
	}

	/**
	 * Convert infix (normal) math expression to postfix.
	 *
	 * @param $math_array
	 *
	 * @return array|string
	 */
	private static function convert_to_postfix( $math_array ) {
		$output    = array();
		$operators = array();

		foreach ( $math_array as $index => $element ) {
			if ( is_numeric( $element ) ) {
				array_push( $output, $element );
			} elseif ( self::is_operator( $element ) ) {
				self::process_next_operator_in_postfix_conversion( $output, $operators, $element );
			} elseif ( '(' === $element ) {
				array_push( $operators, $element );
			} elseif ( ')' === $element ) {
				$status = self::process_right_paren_in_postfix_conversion( $output, $operators, $element );
				if ( false === $status ) {
					return 'error';
				}
			}
		}

		$status = self::move_remaining_operators_to_stack( $output, $operators );

		if ( false === $status ) {
			return 'error';
		}

		return $output;
	}

	/**
	 * Determine if a given element is an operator.
	 *
	 * @param $element
	 *
	 * @return bool
	 */
	private static function is_operator( $element ) {
		return self::operator_precedence( $element ) > 0;
	}

	/**
	 * Return precedence of operator.  Higher is greater precedence.
	 *
	 * @param $operator
	 *
	 * @return int
	 */
	private static function operator_precedence( $operator ) {

		switch ( $operator ) {
			case '-':
				return 5;
			case '+':
				return 5;
			case '*':
				return 7;
			case '/':
				return 7;
			case '%':
				return 7;
			default:
				return 0;
		}
	}

	/**
	 * Process operators in conversion to postfix expression.
	 *
	 * @param $output
	 * @param $operators
	 * @param $operator
	 */
	// postfix rules: http://csis.pace.edu/~wolf/CS122/infix-postfix.htm
	private static function process_next_operator_in_postfix_conversion( &$output, &$operators, $operator ) {
		if ( count( $operators ) > 0 ) {
			$current_element_precedence = self::operator_precedence( $operator );
			do {
				$top_operator            = array_pop( $operators );
				$top_operator_precedence = self::operator_precedence( $top_operator );
				if ( $current_element_precedence <= $top_operator_precedence ) {
					array_push( $output, $top_operator );
				} else {
					// replace last top operator in operator stack; it was only removed for testing
					array_push( $operators, $top_operator );
				}
			} while ( count( $operators ) > 0 && $current_element_precedence <= $top_operator_precedence );
		}
		array_push( $operators, $operator );
	}

	/**
	 * Process right parenthesis in conversion to postfix expression.
	 *
	 * @param $output
	 * @param $operators
	 *
	 * @return bool
	 */
	private static function process_right_paren_in_postfix_conversion( &$output, &$operators ) {
		if ( count( $operators ) === 0 ) {
			return false;
		}
		do {
			$next_operator = array_pop( $operators );
			if ( $next_operator !== '(' ) {
				array_push( $output, $next_operator );
				if ( count( $operators ) === 0 ) {
					return false;
				}
			}
		} while ( $next_operator !== '(' );

		return true;
	}

	/**
	 * Move remaining operators to output stack in conversion to postfix expression.
	 *
	 * @param $output
	 * @param $operators
	 *
	 * @return bool
	 */
	private static function move_remaining_operators_to_stack( &$output, &$operators ) {
		while ( count( $operators ) > 0 ) {
			$next_operator = array_pop( $operators );
			if ( $next_operator === '(' ) {
				return false;
			}
			array_push( $output, $next_operator );
		}

		return true;
	}

	/**
	 * Evaluate postfix expression mathematically.
	 *
	 * @param $postfix_array
	 *
	 * @return mixed|string
	 */
	private static function evaluate_postfix_expression( $postfix_array ) {
		$stack = array();
		foreach ( $postfix_array as $index => $element ) {
			if ( is_numeric( $element ) ) {
				array_push( $stack, $element );
			} else {
				if ( count( $stack ) >= 2 ) {
					$operand2 = array_pop( $stack );
					$operand1 = array_pop( $stack );
					$result   = self::evaluate_simple_math_expression( $operand1, $operand2, $element );
					if ( ! is_numeric( $result ) ) {
						return 'error';
					}
					array_push( $stack, $result );
				} else {
					return 'error';
				}
			}
		}
		if ( count( $stack ) === 1 ) {
			$answer = array_pop( $stack );
			if ( is_numeric( $answer ) ) {
				return $answer;
			}
		}

		return 'error';
	}

	/**
	 * Perform simple arithmetic on two operands.
	 *
	 * @param $operand1
	 * @param $operand2
	 * @param $operator
	 *
	 * @return float|int
	 */
	private static function evaluate_simple_math_expression( $operand1, $operand2, $operator ) {
		$result = 'error';

		switch ( $operator ) {
			case '-':
				$result = $operand1 - $operand2;
				break;
			case '+':
				$result = $operand1 + $operand2;
				break;
			case '*':
				$result = $operand1 * $operand2;
				break;
			case '%':
				if ( $operand2 != 0 ) {
					$result = $operand1 % $operand2;
				}
				break;
			case '/':
				if ( $operand2 != 0 ) {
					$result = $operand1 / $operand2;
				}
				break;
		}

		return $result;
	}
}
