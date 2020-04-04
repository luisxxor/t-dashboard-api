<?php

use App\Models\Subscriptions\Plan;
use App\Models\Subscriptions\PlanFeature;
use App\Models\Subscriptions\PlanProject;
use Illuminate\Database\Seeder;

class PlansTableSeader extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // create plan
        $plan = Plan::create( [
            'name' => 'Plan de pagar por descarga',
            'description' => 'Plan de pagar por descarga',
            'price' => 0.00,
            'signup_fee' => 0.00,
            'invoice_period' => 0,
            'invoice_interval' => 'month',
            'trial_period' => 0,
            'trial_interval' => 'day',
            'sort_order' => 1,
            'currency' => 'PEN',
        ] );
        $plan->features()->saveMany( [
            new PlanFeature( [ 'name' => 'Pagar por descarga', 'value' => 'true', 'sort_order' => 1 ] ),
        ] );

        // create planProject pe-properties
        $planProject = PlanProject::create( [ 'project_code' => 'pe-properties', 'plan_id' => $plan->id ] );

        // create plan
        $plan = Plan::create( [
            'name' => 'Plan de descargas mensuales limitadas',
            'description' => 'Plan de descargas mensuales limitadas',
            'price' => 9.99,
            'signup_fee' => 1.99,
            'invoice_period' => 1,
            'invoice_interval' => 'month',
            'trial_period' => 15,
            'trial_interval' => 'day',
            'sort_order' => 2,
            'currency' => 'PEN',
        ] );
        $plan->features()->saveMany( [
            new PlanFeature( [ 'name' => 'Descargas mensuales limitadas', 'value' => '3', 'sort_order' => 2 ] ),
        ] );

        // create planProject pe-properties
        $planProject = PlanProject::create( [ 'project_code' => 'pe-properties', 'plan_id' => $plan->id ] );
    }
}
