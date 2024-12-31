<div>
    <flux:modal.trigger :name="$this->action->getName()">
        <flux:button
            :icon="$this->action->getIcon()"
            :variant="$this->action->getVariant()"
            type="button"
            size="sm">
            {{ $this->action->getLabel() }}
        </flux:button>
    </flux:modal.trigger>

    @teleport('body')
        <flux:modal
            class="space-y-6"
            :dismissible="$this->action->isDismissable()"
            :name="$this->action->getName()">
            <div>
                <flux:heading size="lg">{{ $this->action->getLabel() }}</flux:heading>
            </div>
            @livewire($this->action->getComponent(), ['action' => $this->action])
        </flux:modal>
    @endteleport
</div>
