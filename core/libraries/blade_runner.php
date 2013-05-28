<?php

class Blade_runner
{
    /**
     * Rewrites the specified string
     *
     * @param  string $value
     * @return string
     */
    public static function rewrite($value)
    {
        // PHP 5.2 does not support the 'static' keyword
        // So have to default to 'self' instead
        $value = self::rewrite_echos($value);
        $value = self::rewrite_openings($value);
        $value = self::rewrite_closings($value);

        return $value;
    }

    /**
     * Rewrites Blade echo statements into PHP echo statements.
     *
     * @param  string $value
     * @return string
     */
    private static function rewrite_echos($value)
    {
        return preg_replace('/\{\{(.+)\}\}/', '<?php echo $1; ?>', $value);
    }

    /**
     * Rewrites Blade structure openings into PHP structure openings.
     *
     * @param  string $value
     * @return string
     */
    private static function rewrite_openings($value)
    {
        return preg_replace('/@(if|foreach|for|while)(.*)/', '<?php $1$2: ?>', $value);
    }

    /**
     * Rewrites Blade structure closings into PHP structure closings.
     *
     * @param  string $value
     * @return string
     */
    private static function rewrite_closings($value)
    {
        $value = preg_replace('/(\s*)@(else|elseif)(.*)/', '$1<?php $2$3: ?>', $value);
        $value = preg_replace('/^(\s*)@(endif|endforeach|endfor|endwhile)(\s*)$/m', '$1<?php $2; ?> $3', $value);

        return $value;
    }

}
