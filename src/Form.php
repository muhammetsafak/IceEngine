<?php
/**
 * Form.php
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

class Form
{
    protected string $output = '';

    public function clear()
    {
        $this->output = '';
    }

    public function new(): Form
    {
        $clone = clone $this;
        $clone->clear();
        return $clone;
    }

    public function output(): string
    {
        $output = $this->output;
        $this->clear();
        return $output;
    }

    public function start(string $action, string $method = 'POST', array $attributes = []): self
    {
        $this->output .= '<form';
        if(!empty($action)){
            $this->output .= ' action="' . $action . '"';
        }
        $this->output .= ' method="' . $method . '"' . $this->setAttributes($attributes) . '>';
        return $this;
    }

    public function end(): self
    {
        $this->output .= '</form>';
        return $this;
    }

    public function label(string $label, string $for): self
    {
        $this->output .= '<label for="'.\trim($for).'">'.$label.'</label>';
        return $this;
    }

    public function input(string $name, string $type = 'text', array $attributes = []): self
    {
        $name = \trim($name);
        $this->output .= '<input type="'.\trim($type).'"';
        if(!empty($name)){
            $this->output .= ' name="' . $name . '"';
        }
        $this->output .= $this->setAttributes($attributes) . ' />';
        return $this;
    }

    public function submit(string $value = 'Submit'): self
    {
        return $this->input('', 'submit', ['value' => $value]);
    }

    public function button(string $value, array $attributes = []): self
    {
        $this->output .= '<button' . $this->setAttributes($attributes) . '>' . $value . '</button>';
        return $this;
    }

    public function textarea(string $name, string $value = '', array $attributes = []): self
    {
        $name = \trim($name);
        $this->output .= '<textarea';
        if(!empty($name)){
            $this->output .= ' name="' . $name . '"';
        }
        $this->output .= $this->setAttributes($attributes) . '>' . $value . '</textarea>';
        return $this;
    }

    public function select(string $name, array $options, array $attributes = [], ?string $selected = null): self
    {
        $this->output .= '<select name="' . \trim($name) . '"' . $this->setAttributes($attributes) . '>';
        foreach ($options as $key => $value){
            $this->output .= '<option value="' . $key . '"' . ($selected === $key ? ' selected' :'') . '>' . $value . '</option>';
        }
        $this->output .= '</select>';
        return $this;
    }

    public function html(string $html): self
    {
        $this->output .= $html;
        return $this;
    }

    protected function setAttributes(array $attributes = []): string
    {
        $str = '';
        if(!empty($attributes)){
            $atts = [];
            foreach ($attributes as $key => $val){
                if(\is_int($key)){
                    $atts[] = $val;
                }else{
                    $atts[] = $key . '="' . $val . '"';
                }
            }
            $str .= ' ' . \implode(' ', $atts);
        }
        return $str;
    }

}
