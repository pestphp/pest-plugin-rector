<?php

class ProductionService
{
    public function handle(): void
    {
        try {
            $this->api->call();
        } catch (\Exception $exception) {
            try {
                $this->api->performActionsImmediately(true);
            } catch (\Exception) {
            }
        }
    }
}
