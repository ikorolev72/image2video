#!/bin/bash
# korolev-ia [] yandex.ru
# This job_starter
# check the new files, check the queue and run tasks
# 

BASENAME=`basename $0`
cd `dirname $0`
DIRNAME=`pwd`
. "$DIRNAME/common.sh"

PROCESS_LOG=$QUEUE_DIR/job_starter.log


DATE=`date +%Y-%m-%d_%H:%M:%S`
MY_PID_FILE="${PID_DIR}/job_starter.pid" 
if [ -f  $MY_PID_FILE ]; then 
	ps --pid `cat $MY_PID_FILE` -o cmd h  >/dev/null 2>&1 
	if [ $? -eq 0 ]; then
		# previrouse job_starter don't finished yet
		exit 0
	fi				
fi
echo  $$  > $MY_PID_FILE


########## check failed jobs
# check downloader jobs

for id in  $( sqlite3 $DB 'select id from tasks where status="running"' ); do
	pid_file="$PID_DIR/$id.pid"
	ps --pid `cat $pid_file` -o cmd h >/dev/null 2>&1 
	if [ $? -ne 0 ]; then
		# remove pid-files for failed job ( process crashed or killed )
		rm $pid_file
		sqlite3 $DB "update tasks set status='failed' where id=$id"
	fi		
done
# end of check


########## run jobs in the queues
for queue $( find $QUEUES  -maxdepth 1 -type 'd' ); do
	if [ ! -f  "$queue/queuename.txt" ]; then
		continue
	fi
	QUEUE_NAME=$( cat "$queue/queuename.txt")
	JOB=$( $DIRNAME/pop_job.sh $QUEUE_NAME )
	if [ $? -ne 0 ]; then
		# queue is empty
		break
	fi
	export $JOB
	/bin/bash -c 'source $JOB; \
					$$>$PID; \
					$COMMAND >> $LOG 2>&1 ; \
					if [ $? -ne 0 ]; then mv $QUEUE_DIR/$JOB_ID.started $QUEUE_DIR/$JOB_ID.running > $status; else echo success > $status; fi;\
					rm $PID' &
done


rm -rf $MY_PID_FILE
exit 0


#
