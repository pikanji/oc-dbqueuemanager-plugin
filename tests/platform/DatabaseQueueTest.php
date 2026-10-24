<?php namespace Pikanji\DbQueueManager\Tests\Platform;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Queue\DatabaseQueue;
use Pikanji\DbQueueManager\Models\Job;
use Pikanji\DbQueueManager\Tests\PlatformTestCase;

class DatabaseQueueTest extends PlatformTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->database->getConnection()->getSchemaBuilder()->create('jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('queue');
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });
    }

    private function queue(): DatabaseQueue
    {
        $queue = new DatabaseQueue($this->database->getConnection(), 'jobs', 'default', 60);
        $queue->setContainer($this->app);
        return $queue;
    }

    public function testLaravelPayloadAndUnixTimestampsAreReadableByManagerModel(): void
    {
        $timestamp = 1700000000;
        $id = $this->database->getConnection()->table('jobs')->insertGetId([
            'queue' => 'invalidationCheck', 'payload' => json_encode(['displayName' => '日本語ジョブ', 'data' => ['id' => 42]]),
            'attempts' => 0, 'reserved_at' => null, 'available_at' => $timestamp, 'created_at' => $timestamp,
        ]);
        $job = Job::findOrFail($id);
        self::assertSame($timestamp, $job->created_at->getTimestamp());
        self::assertSame($timestamp, $job->available_at->getTimestamp());
        self::assertSame('日本語ジョブ', json_decode($job->payload, true)['displayName']);
        self::assertSame('invalidationCheck', $job->queue);
    }

    public function testEditingJobDoesNotWriteNonexistentUpdatedAtColumn(): void
    {
        $id = $this->queue()->pushRaw('{"displayName":"test"}', 'invalidationCheck');
        $job = Job::findOrFail($id);
        $job->queue = 'bulkFileManipulation';
        $job->save();
        self::assertSame('bulkFileManipulation', Job::findOrFail($id)->queue);
    }

    public function testEditingAvailabilityKeepsUnixTimestampFormat(): void
    {
        $id = $this->queue()->pushRaw('{"displayName":"test"}', 'invalidationCheck');
        $job = Job::findOrFail($id);
        $created = $job->getRawOriginal('created_at');
        $job->available_at = new \DateTimeImmutable('2030-01-01T00:00:00Z');
        $job->save();
        $row = $this->database->getConnection()->table('jobs')->find($id);
        self::assertSame(1893456000, (int)$row->available_at);
        self::assertSame((int)$created, (int)$row->created_at);
    }

    public function testDeletingOneJobPreservesJobsInOtherQueues(): void
    {
        $queue = $this->queue();
        $first = $queue->pushRaw('{"displayName":"one"}', 'invalidationCheck');
        $second = $queue->pushRaw('{"displayName":"two"}', 'bulkFileManipulation');
        Job::findOrFail($first)->delete();
        self::assertNull(Job::find($first));
        self::assertSame('bulkFileManipulation', Job::findOrFail($second)->queue);
    }

    public function testWorkerReservesAndDeletesManagerVisibleJob(): void
    {
        $queue = $this->queue();
        $id = $queue->pushRaw('{"displayName":"no-network","job":"unused","data":{}}', 'invalidationCheck');
        $reserved = $queue->pop('invalidationCheck');
        self::assertSame((string)$id, (string)$reserved->getJobId());
        self::assertSame(1, (int)Job::findOrFail($id)->attempts);
        self::assertNotNull(Job::findOrFail($id)->getRawOriginal('reserved_at'));
        // Never fire the payload. This tests the real Laravel reservation contract only.
        $reserved->delete();
        Job::flushDuplicateCache(); // A worker changes the DB outside Winter's model layer.
        self::assertNull(Job::find($id));
    }

    public function testFutureJobIsVisibleToManagerButNotReservedByWorker(): void
    {
        $queue = $this->queue();
        $this->database->getConnection()->table('jobs')->insert([
            'queue' => 'invalidationCheck', 'payload' => '{"displayName":"delayed"}',
            'attempts' => 0, 'reserved_at' => null, 'available_at' => time() + 3600, 'created_at' => time(),
        ]);
        self::assertSame(1, Job::count());
        self::assertNull($queue->pop('invalidationCheck'));
    }
}
