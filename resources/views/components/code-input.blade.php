@props([
    'wire:model' => null,
    'error' => false,
])

<div x-data="codeInput()" x-init="init()" class="fi-code-input">
    <div class="grid grid-cols-6 gap-2 sm:gap-3">
        @for ($i = 0; $i < 6; $i++)
            <input
                type="text"
                inputmode="numeric"
                pattern="[0-9]"
                maxlength="1"
                x-ref="input{{ $i }}"
                x-model="digits[{{ $i }}]"
                @input="handleInput({{ $i }}, $event)"
                @keydown="handleKeydown({{ $i }}, $event)"
                @paste="handlePaste($event)"
                class="fi-input block w-full rounded-lg text-center text-2xl font-semibold shadow-sm transition duration-75
                       {{ $error ? 'border-danger-600 ring-danger-600 dark:border-danger-400 dark:ring-danger-400' : 'border-gray-300 dark:border-gray-600' }}
                       focus:border-primary-600 focus:ring-1 focus:ring-inset focus:ring-primary-600
                       disabled:bg-gray-50 disabled:text-gray-500 disabled:opacity-70
                       dark:bg-white/5 dark:text-white dark:focus:border-primary-500 dark:focus:ring-primary-500
                       dark:disabled:bg-transparent dark:disabled:text-gray-400"
            />
        @endfor
    </div>

    <input
        type="hidden"
        {{ $attributes->wire('model') }}
        x-model="code"
    />
</div>

@script
<script>
Alpine.data('codeInput', () => ({
    digits: ['', '', '', '', '', ''],
    code: '',

    init() {
        this.$nextTick(() => {
            this.$refs.input0.focus();
        });

        // Listen for clear event from Livewire
        Livewire.on('clear-code-input', () => {
            this.clear();
        });
    },

    handleInput(index, event) {
        const value = event.target.value;

        // Only allow digits
        if (value && !/^\d$/.test(value)) {
            this.digits[index] = '';
            return;
        }

        // Update the code
        this.updateCode();

        // Move to next input if digit entered
        if (value && index < 5) {
            this.$refs[`input${index + 1}`].focus();
        }

        // Auto-submit if all digits filled
        if (this.digits.every(d => d !== '')) {
            this.$nextTick(() => {
                this.$root.closest('form').dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
            });
        }
    },

    handleKeydown(index, event) {
        // Handle backspace
        if (event.key === 'Backspace') {
            if (!this.digits[index] && index > 0) {
                event.preventDefault();
                this.$refs[`input${index - 1}`].focus();
                this.digits[index - 1] = '';
                this.updateCode();
            }
        }
        // Handle arrow keys
        else if (event.key === 'ArrowLeft' && index > 0) {
            event.preventDefault();
            this.$refs[`input${index - 1}`].focus();
        }
        else if (event.key === 'ArrowRight' && index < 5) {
            event.preventDefault();
            this.$refs[`input${index + 1}`].focus();
        }
        // Prevent non-numeric input
        else if (event.key.length === 1 && !/^\d$/.test(event.key)) {
            event.preventDefault();
        }
    },

    handlePaste(event) {
        event.preventDefault();
        const pastedData = event.clipboardData.getData('text');

        // Extract only digits from pasted content
        const digits = pastedData.replace(/\D/g, '').slice(0, 6);

        if (digits.length === 6) {
            // Fill all inputs
            for (let i = 0; i < 6; i++) {
                this.digits[i] = digits[i];
            }

            // Update code
            this.updateCode();

            // Focus last input
            this.$refs.input5.focus();

            // Auto-submit
            this.$nextTick(() => {
                this.$root.closest('form').dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
            });
        }
    },

    updateCode() {
        this.code = this.digits.join('');
    },

    clear() {
        this.digits = ['', '', '', '', '', ''];
        this.code = '';
        this.$nextTick(() => {
            this.$refs.input0.focus();
        });
    }
}));
</script>
@endscript
