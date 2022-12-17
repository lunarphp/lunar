<?php

namespace Lunar\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Currency;
use Lunar\Models\JobBatch;
use Lunar\Tests\Stubs\User as StubUser;
use Lunar\Tests\TestCase;

/**
 * @group lunar.models
 */
class JobBatchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Updates given job batch
     * @param JobBatch $jobBatch
     * @param array $data
     * @return void
     */
    private function updateJobBatch(JobBatch $jobBatch, array $data)
    {
        $jobBatch
            ->getConnection()
            ->table($jobBatch->getTable())
            ->where('id', $jobBatch->getKey())
            ->update($data);
    }

    /** @test */
    public function can_create_a_job_batch()
    {
        $name = sprintf('another job batch: %s', date('Ymd-His'));
        $jobBatch = JobBatch::factory()->create([
            'name' => $name,
        ]);

        $this->assertEquals($jobBatch->name, $name);
    }

    /** @test */
    public function provides_scope_for_specific_subject()
    {
        $subject = Currency::factory()->create();
        $causer = StubUser::factory()->create();

        $jobBatch = JobBatch::factory()->create([
            'name' => 'nice job batch'
        ]);

        // associate the subject with the job batch
        $jobBatch
            ->subject()
            ->associate($subject)
            ->causer()
            ->associate($causer)
            ->save();

        $jobBatches = JobBatch::forSubject($subject)->get();

        $this->assertCount(1, $jobBatches);
        $this->assertEquals($jobBatches->first()->subject_id, $subject->getKey());
        $this->assertEquals($jobBatches->first()->subject_type, $subject->getMorphClass());
        $this->assertEquals($jobBatches->first()->name, 'nice job batch');

        // associate another subject with the job batch
        $jobBatch
            ->subject()
            ->associate(Currency::factory()->create([
                'name' => 'another currency'
            ]))
            ->save();

        $jobBatches = JobBatch::forSubject($subject)->get();

        $this->assertCount(0, $jobBatches);
    }

    /** @test */
    public function provides_scope_for_specific_causer()
    {
        $subject = Currency::factory()->create();
        $causer = StubUser::factory()->create();

        $jobBatch = JobBatch::factory()->create([
            'name' => 'nice job batch'
        ]);

        // associate the causer with the job batch
        $jobBatch
            ->subject()
            ->associate($subject)
            ->causer()
            ->associate($causer)
            ->save();

        $jobBatches = JobBatch::causedBy($causer)->get();

        $this->assertCount(1, $jobBatches);
        $this->assertEquals($jobBatches->first()->causer_id, $causer->getKey());
        $this->assertEquals($jobBatches->first()->causer_type, $causer->getMorphClass());
        $this->assertEquals($jobBatches->first()->name, 'nice job batch');

        // associate another causer with the job batch
        $jobBatch
            ->causer()
            ->associate(StubUser::factory()->create([
                'name' => 'another user'
            ]))
            ->save();

        $jobBatches = JobBatch::causedBy($causer)->get();

        $this->assertCount(0, $jobBatches);
    }

    /** @test */
    public function job_batch_status_accessor_is_correct()
    {
        $jobBatch = JobBatch::factory()->create([
            'total_jobs' => 100,
            'pending_jobs' => 20,
            'failed_jobs' => 5,
        ]);
        $this->assertEquals(JobBatch::STATUS_PENDING, $jobBatch->status);

        $jobBatch->finished_at = time();
        $jobBatch->pending_jobs = 0;
        $this->assertEquals(JobBatch::STATUS_UNHEALTHY, $jobBatch->status);

        $jobBatch->failed_jobs = 100;
        $this->assertEquals(JobBatch::STATUS_FAILED, $jobBatch->status);

        $jobBatch->pending_jobs = 0;
        $jobBatch->failed_jobs = 0;
        $jobBatch->finished_at = time();
        $jobBatch->cancelled_at = null;
        $this->assertEquals(JobBatch::STATUS_SUCCESSFUL, $jobBatch->status);
    }

}
