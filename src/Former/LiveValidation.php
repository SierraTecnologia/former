<?php
namespace Former;

use Former\Traits\Field;

/**
 * Handles the transformation of validation rules into actual
 * attributes and patterns for HTML5 live validation
 */
class LiveValidation
{
    /**
     * The field being worked on
     *
     * @var Field
     */
    public $field;

    /**
     * Load a Field instance to apply rules to it
     *
     * @param Field $field The field
     */
    public function __construct(Field &$field)
    {
        $this->field = $field;
    }

    /**
     * Apply live validation rules to a field
     *
     * @param array $rules The rules to apply
     *
     * @return false|null
     */
    public function apply($rules)
    {
        // If no rules to apply, cancel
        if (!$rules) {
            return false;
        }

        foreach ($rules as $rule => $parameters) {

            // If the rule is unsupported yet, skip it
            if (!method_exists($this, $rule)) {
                continue;
            }

            $this->$rule($parameters);
        }
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////////// RULES /////////////////////////////
    ////////////////////////////////////////////////////////////////////

    // Field types
    ////////////////////////////////////////////////////////////////////

    /**
     * Email field
     *
     * @return void
     */
    public function email(): void
    {
        $this->field->setType('email');
    }

    /**
     * URL field
     *
     * @return void
     */
    public function url(): void
    {
        $this->field->setType('url');
    }

    /**
     * Required field
     *
     * @return void
     */
    public function required(): void
    {
        $this->field->required();
    }

    // Patterns
    ////////////////////////////////////////////////////////////////////

    /**
     * Integer field
     *
     * @return void
     */
    public function integer(): void
    {
        $this->field->pattern('\d+');
    }

    /**
     * Numeric field
     *
     * @return void
     */
    public function numeric(): void
    {
        if ($this->field->isOfType('number')) {
            $this->field->step('any');
        } else {
            $this->field->pattern('[+-]?\d*\.?\d+');
        }
    }

    /**
     * Not numeric field
     *
     * @return void
     */
    public function not_numeric(): void
    {
        $this->field->pattern('\D+');
    }

    /**
     * Only alphanumerical
     *
     * @return void
     */
    public function alpha(): void
    {
        $this->field->pattern('[a-zA-Z]+');
    }

    /**
     * Only alphanumerical and numbers
     *
     * @return void
     */
    public function alpha_num(): void
    {
        $this->field->pattern('[a-zA-Z0-9]+');
    }

    /**
     * Alphanumerical, numbers and dashes
     *
     * @return void
     */
    public function alpha_dash(): void
    {
        $this->field->pattern('[a-zA-Z0-9_\-]+');
    }

    /**
     * In []
     *
     * @return void
     */
    public function in($possible): void
    {
        // Create the corresponding regex
        $possible = (sizeof($possible) == 1) ? $possible[0] : '('.join('|', $possible).')';

        $this->field->pattern('^'.$possible.'$');
    }

    /**
     * Not in []
     *
     * @return void
     */
    public function not_in($impossible): void
    {
        $this->field->pattern('(?:(?!^'.join('$|^', $impossible).'$).)*');
    }

    /**
     * Matches a pattern
     *
     * @return void
     */
    public function match($pattern): void
    {
        // Remove delimiters from existing regex
        $pattern = substr($pattern[0], 1, -1);

        $this->field->pattern($pattern);
    }

    /**
     * Alias for match
     */
    public function regex($pattern)
    {
        return $this->match($pattern);
    }

    // Boundaries
    ////////////////////////////////////////////////////////////////////

    /**
     * Max value
     *
     * @return void
     */
    public function max($max): void
    {
        if ($this->field->isOfType('file')) {
            $this->size($max);
        } else {
            $this->setMax($max[0]);
        }
    }

    /**
     * Max size
     *
     * @return void
     */
    public function size($size): void
    {
        $this->field->max($size[0]);
    }

    /**
     * Min value
     *
     * @return void
     */
    public function min($min): void
    {
        $this->setMin($min[0]);
    }

    /**
     * Set boundaries
     *
     * @return void
     */
    public function between($between): void
    {
        list($min, $max) = $between;

        $this->setBetween($min, $max);
    }

    /**
     * Set accepted mime types
     *
     * @param string[] $mimes
     *
     * @return false|null
     */
    public function mimes($mimes)
    {
        // Only useful on file fields
        if (!$this->field->isOfType('file')) {
            return false;
        }

        $this->field->accept($this->setAccepted($mimes));
    }

    /**
     * Set accept only images
     *
     * @return void
     */
    public function image(): void
    {
        $this->mimes(array('jpg', 'png', 'gif', 'bmp'));
    }

    // Dates
    ////////////////////////////////////////////////////////////////////

    /**
     * Before a date
     *
     * @return void
     */
    public function before($date): void
    {
        list($format, $date) = $this->formatDate($date[0]);

        $this->field->max(date($format, $date));
    }

    /**
     * After a date
     *
     * @return void
     */
    public function after($date): void
    {
        list($format, $date) = $this->formatDate($date[0]);

        $this->field->min(date($format, $date));
    }

    ////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS /////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Transform extensions and mime groups into a list of mime types
     *
     * @param array $mimes An array of mimes
     *
     * @return string A concatenated list of mimes
     */
    private function setAccepted($mimes)
    {
        // Transform extensions or mime groups into mime types
        $mimes = array_map(array('\Laravel\File', 'mime'), $mimes);

        return implode(',', $mimes);
    }

    /**
     * Format a date to a pattern
     *
     * @param string $date The date
     *
     * @return (false|int|string)[] The pattern
     *
     * @psalm-return array{0: string, 1: false|int}
     */
    private function formatDate($date): array
    {
        $format = 'Y-m-d';

        // Add hour for datetime fields
        if ($this->field->isOfType('datetime', 'datetime-local')) {
            $format .= '\TH:i:s';
        }

        return array($format, strtotime($date));
    }

    /**
     * Set a maximum value to a field
     *
     * @param integer $max
     *
     * @return void
     */
    private function setMax($max): void
    {
        $attribute = $this->field->isOfType('number') ? 'max' : 'maxlength';

        $this->field->$attribute($max);
    }

    /**
     * Set a minimum value to a field
     *
     * @param integer $min
     *
     * @return void
     */
    private function setMin($min): void
    {
        if ($this->field->isOfType('number') == 'min') {
            $this->field->min($min);
        } else {
            $this->field->pattern(".{".$min.",}");
        }
    }

    /**
     * Set a minimum and maximum value to a field
     *
     * @param $min
     * @param $max
     *
     * @return void
     */
    public function setBetween($min, $max): void
    {
        if ($this->field->isOfType('number') == 'min') {
            // min, max values for generation of the pattern
            $this->field->min($min);
            $this->field->max($max);
        } else {
            $this->field->pattern('.{'.$min.','.$max.'}');

            // still let the browser limit text input after reaching the max
            $this->field->maxlength($max);
        }
    }
}
