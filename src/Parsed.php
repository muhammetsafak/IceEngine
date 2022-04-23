<?php
/**
 * Parsed.php
 *
 * This file is part of IceEngine.
 *
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2022 IceEngine
 * @license    https://github.com/muhametsafak/IceEngine/blob/main/LICENSE  MIT
 * @version    0.3
 * @link       https://www.muhammetsafak.com.tr
 */

declare(strict_types=1);

namespace IceEngine;

use \RuntimeException;
use \InvalidArgumentException;

class Parsed
{

    protected ?string $content = null;

    protected ?string $prepare = null;

    protected static array $yields = [];

    protected static array $data = [];

    protected static array $directive = [];


    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->prepare;
    }

    public function addDirective(string $directive, \Closure $closure)
    {
        $directive = \ltrim(\trim($directive), '@');
        if(\preg_match('/([\w]+)/i', $directive) === FALSE){
            throw new RuntimeException('Directive can only contain alphanumeric characters.');
        }
        self::$directive[$directive] = $closure;
    }

    public function parse(): self
    {
        $this->prepare = $this->internalFunctions($this->content);
        return $this;
    }


    private function internalFunctions(string $content): string
    {
        /**
         * Extends
         */
        $content = \preg_replace_callback('/@extends\((.+)\)/iUs', function($extend){
            $extends = \trim($extend[1], " \t\n\r\0\x0B\"'");
            return $this->read($extends);
        }, $content);


        /**
         * Single Section
         */
        $content = \preg_replace_callback('/@section\((.+),(.+)\)/iUs', function ($section){
            $key = \trim($section[1], " \t\n\r\0\x0B\"'");
            self::$yields[$key] = \trim($section[2], " \t\n\r\0\x0B\"'");
            return '';
        }, $content);

        /**
         * Section
         */
        $content = \preg_replace_callback('/@section\((.+)\)(.*)@endsection/iUs', function ($content){
            $key = \trim($content[1], " \t\n\r\0\x0B\"'");
            $value = $this->internalFunctions($content[2]);
            self::$yields[$key] = $value;
            return '';
        }, $content);

        /**
         * Include
         */
        $content = \preg_replace_callback('/@include\((.*)\)/iUs', function ($include){
            $viewName = \trim($include[1], " \t\n\r\0\x0B\"'");
            return $this->read($viewName);
        }, $content);


        /**
         * Form
         */
        $content = $this->formRender($content);

        /**
         * ENV
         */
        $content = \preg_replace_callback('/@env\((.*)\)/iUs', function ($env){
            $env = \trim($env, " \t\n\r\0\x0B\"'");
            return '<?php if(isset($_ENV["ENVIRONMENT"]) && $_ENV["ENVIRONMENT"] === "' . $env . '"): ?>';
        }, $content);
        $content = \preg_replace('/@endenv/iUs', '<?php endif; ?>', $content);


        /**
         * ForEach Loops
         */
        $content = \preg_replace_callback('/@foreach\((.*)\)/iUs', function ($expression){
            return '<?php foreach (' . \trim($expression[1]) . '): ?>';
        }, $content);
        $content = \preg_replace('/@endforeach/iUs', "<?php endforeach; ?>", $content);

        /**
         * For Loops
         */
        $content = \preg_replace_callback('/@for\((.*)\)/iUs', function($expression){
            return '<?php for(' . \trim($expression[1]) . '): ?>';
        }, $content);
        $content = \preg_replace('/@endfor/iUs', "<?php endfor; ?>", $content);


        /**
         * While Loops
         */
        $content = \preg_replace_callback('/@while\((.*)\)/iUs', function($expression){
            return '<?php while(' . \trim($expression[1]) . '): ?>';
        }, $content);
        $content = \preg_replace('/@endwhile/iUs', "<?php endwhile; ?>", $content);

        /**
         * Continue and Break
         */
        $content = \preg_replace_callback('/@continue\((.*)\)/iUs', function ($condition){
            return '<?php if(' . \trim($condition[1]) . '){ continue; } ?>';
        }, $content);
        $content = \preg_replace_callback('/@break\((.*)\)/iUs', function ($condition){
            return '<?php if(' . \trim($condition[1]) . '){ break; } ?>';
        }, $content);
        $content = \preg_replace('/@continue/iUs', "<?php continue; ?>", $content);
        $content = \preg_replace('/@break/iUs', "<?php break; ?>", $content);

        /**
         * If/ElseIf/Else
         */
        $content = \preg_replace_callback('/@if\((.*?)\)/iUs', function($expression){
            return '<?php if(' . \trim($expression[1]) . '): ?>';
        }, $content);
        $content = \preg_replace_callback('/@elseif\((.*?)\)/iUs', function($expression){
            return '<?php elseif(' . \trim($expression[1]) . '): ?>';
        }, $content);
        $content = \preg_replace('/@else/iUs', "<?php else: ?>", $content);
        $content = \preg_replace('/@endif/iUs', "<?php endif; ?>", $content);

        /**
         * SWITCH/Case
         */
        $content = \preg_replace_callback('/@switch\((.*)\)(.*)@endswitch/iUs', function ($expression){
            $return = '<?php switch ('.\trim($expression[1]).'): ?>';
            $return .= $this->internalFunctions(\trim($expression[2]));
            $return .= '<?php endswitch; ?>';
            return $return;
        }, $content);

        $content = \preg_replace_callback('/@case\((.*)\)/iUs', function ($expression){
            return '<?php case '.\trim($expression[1]).': ?>';
        }, $content);
        $content = \preg_replace('/@default/iUs', "<?php default: ?>", $content);

        /**
         * Isset
         */
        $content = \preg_replace_callback('/@isset\((.*?)\)/iUs', function($expression){
            return '<?php if(isset(' . \trim($expression[1]) . ')): ?>';
        }, $content);
        $content = \preg_replace('/@endisset/iUs', "<?php endif; ?>", $content);

        /**
         * Empty
         */
        $content = \preg_replace_callback('/@empty\((.*?)\)/iUs', function ($expression){
            return '<?php if(empty(' . \trim($expression[1]) . ')): ?>';
        }, $content);
        $content = \preg_replace('/@endempty/iUs', "<?php endif; ?>", $content);

        /**
         * PHP
         */
        $content = \preg_replace('/@php/iUs', "<?php ", $content);
        $content = \preg_replace('/@endphp/iUs', "?>", $content);

        /**
         * Yields
         */
        $content = \preg_replace_callback('/@yield\((.*)\)/iUs', function ($name){
            $key = \trim($name[1], " \t\n\r\0\x0B\"'");
            return self::$yields[$key] ?? '@yield(' . $name[1] . ')';
        }, $content);

        /**
         * Method
         */
        $content = \preg_replace_callback('/@method\((.*)\)/iUs', function ($method){
            return '<input type="hidden" name="_method" value="' . \trim($method[1], " \t\n\r\0\x0B\"'") . '" />';
        }, $content);


        /**
         * Custom Directive
         */
        foreach (self::$directive as $directive => $callable){
            $content = \preg_replace_callback('/@' . $directive . '(\((.*)\)|)/iUs', function($expression) use ($callable){
                $arguments = [];
                if(isset($expression[2])){
                    $arguments[] = $expression[2];
                }
                return \call_user_func_array($callable, $arguments);
            }, $content);
        }

        /**
         * Comments
         */
        $content = \preg_replace_callback('/{{--(.*)--}}/iUs', function ($comment){
            return '';
        }, $content);

        /**
         * Variables
         */
        $content = $this->renderVariables($content, true);

        return \trim($content);
    }


    protected function formRender(string $content): string
    {
        $content = \preg_replace_callback('/@form\((.*)\)(.*)@endform/iUs', function ($form) {
            $return = '<?php $this->form->start(' . $this->renderVariables(\trim($form[1]), false) . '); ?>';
            $return .= $this->formRender(\trim($form[2]));
            $return .= '<?php $this->form->end(); ?>';
            return $return . \PHP_EOL . '<?php echo $this->form->output(); ?>';
        }, $content);

        $content = \preg_replace_callback('/@input\((.*)\)/iUs', function ($input){
            return '<?php $this->form->input(' . $this->renderVariables(\trim($input[1]), false) . '); ?>';
        }, $content);

        $content = \preg_replace_callback('/@textarea\((.*)\)/iUs', function ($textarea){
            return '<?php $this->form->textarea(' . $this->renderVariables(\trim($textarea[1]), false) . '); ?>';
        }, $content);

        $content = \preg_replace_callback('/@submit\((.*)\)/iUs', function ($submit){
            return '<?php $this->form->submit(' . $this->renderVariables(\trim($submit[1]), false) . '); ?>';
        }, $content);

        $content = \preg_replace_callback('/@button\((.*)\)/iUs', function ($button){
            return '<?php $this->form->button(' . $this->renderVariables(\trim($button[1]), false) . '); ?>';
        }, $content);

        $content = \preg_replace_callback('/@label\((.*)\)/iUs', function ($label){
            return '<?php $this->form->label(' . $this->renderVariables(\trim($label[1]), false) . '); ?>';
        }, $content);

        $content = \preg_replace_callback('/@select\((.*)\)/iUs', function ($select){
            return '<?php $this->form->select(' . $this->renderVariables(\trim($select[1]), false) . '); ?>';
        }, $content);

        return $content;
    }

    protected function renderVariables(string $content, bool $phpTags = true): string
    {
        $content = \preg_replace_callback('/{!!(.*)!!}/iUs', function ($vars) use($phpTags) {
            if($phpTags === TRUE){
                return "<?php echo ".\trim($vars[1])."; ?>";
            }
            return \trim($vars[1]);
        }, $content);
        $content = \preg_replace_callback('/[{]{2}(.*)[}]{2}/iUs', function ($vars) use($phpTags) {
            if($phpTags === TRUE){
                return "<?php echo htmlspecialchars(".\trim($vars[1]).", ENT_QUOTES, 'UTF-8'); ?>";
            }
            return "htmlspecialchars(".\trim($vars[1]).", ENT_QUOTES, 'UTF-8')";
        }, $content);

        return $content;
    }


    private function read($name): string
    {
        $path = Engine::viewPath($name);
        if(!\file_exists($path)){
            Engine::error('Replaced with a space because "'.$name.'" could not be found.');
            return '';
        }
        if(($content = @\file_get_contents($path)) === FALSE){
            Engine::error('Replaced "'.$name.'" with an unreadable space.');
            return '';
        }
        return $this->internalFunctions($content);
    }

}
