<?php

namespace Stephenjude\FilamentTwoFactorAuthentication\Forms\Components;

use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;

class DigitInputGroup
{
    public static function make(?callable $validation = null): Group
    {
        $makeDigitInput = function (int $index) use ($validation) {
            $input = TextInput::make("digit{$index}")
                ->hiddenLabel()
                ->maxLength(1)
                ->inputMode('numeric')
                ->extraInputAttributes([
                    'class' => 'text-center !text-2xl !font-semibold aspect-square',
                    'pattern' => '[0-9]',
                    'x-ref' => "digit{$index}",
                    'x-on:input' => "handleInput({$index}, \$event)",
                    'x-on:keydown' => "handleKeydown({$index}, \$event)",
                    'x-on:paste.prevent' => 'handlePaste($event)',
                ])
                ->required();

            // Add validation to the last digit
            if ($index === 5 && $validation) {
                $input->rules([$validation]);
            }

            return $input;
        };

        return Group::make([
            $makeDigitInput(0),
            $makeDigitInput(1),
            $makeDigitInput(2),
            $makeDigitInput(3),
            $makeDigitInput(4),
            $makeDigitInput(5),
        ])
            ->columns(6)
            ->columnSpanFull()
            ->extraAttributes([
                'x-data' => '{
                    init() {
                        this.$nextTick(() => this.$refs.digit0?.focus());
                    },
                    handleInput(index, event) {
                        const input = event.target;
                        const value = input.value;

                        if (value && !/^\d$/.test(value)) {
                            input.value = "";
                            return;
                        }

                        if (value && index < 5) {
                            this.$refs["digit" + (index + 1)]?.focus();
                        }
                    },
                    handleKeydown(index, event) {
                        const input = event.target;

                        if (event.key === "Backspace") {
                            if (!input.value && index > 0) {
                                event.preventDefault();
                                this.$refs["digit" + (index - 1)]?.focus();
                            }
                        } else if (event.key === "ArrowLeft" && index > 0) {
                            event.preventDefault();
                            this.$refs["digit" + (index - 1)]?.focus();
                        } else if (event.key === "ArrowRight" && index < 5) {
                            event.preventDefault();
                            this.$refs["digit" + (index + 1)]?.focus();
                        } else if (event.key.length === 1 && !/^\d$/.test(event.key)) {
                            event.preventDefault();
                        }
                    },
                    handlePaste(event) {
                        event.preventDefault();
                        const pastedData = event.clipboardData.getData("text");
                        const digits = pastedData.replace(/\D/g, "").slice(0, 6);

                        if (digits.length === 6) {
                            for (let i = 0; i < 6; i++) {
                                const input = this.$refs["digit" + i];
                                if (input) {
                                    input.value = digits[i];
                                    input.dispatchEvent(new Event("input", { bubbles: true }));
                                }
                            }
                            this.$refs.digit5?.focus();
                        }
                    }
                }',
            ]);
    }

    public static function combineDigits(array $data): string
    {
        return ($data['digit0'] ?? '') .
               ($data['digit1'] ?? '') .
               ($data['digit2'] ?? '') .
               ($data['digit3'] ?? '') .
               ($data['digit4'] ?? '') .
               ($data['digit5'] ?? '');
    }
}
