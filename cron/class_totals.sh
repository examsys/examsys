#!/bin/bash

# Environment variables in use:
# ROGO - location of ExamSys base directory
# ROGOCRONLOGS - location to log output of script
# ROGOCRONLOCK - location to write the lock file

# Declare variables.
ROGODIR="${ROGO}/testing"
ROGOSCRIPT="class_totals_with_script_cli.php"
TIMESTAMP=$(date +"%Y-%m-%d-%H:%M:%S")
LOGDIR=${ROGOCRONLOGS}/checktotals
LOCKFILE=checktotals

# Check for log directory and create if not there.
if [ ! -d ${LOGDIR} ]; then
	mkdir ${LOGDIR}
fi

LOGFILE="${LOGDIR}/checktotals.log.${TIMESTAMP}"

# Run ExamSys script and log if not already running.
if [ ! -f ${ROGOCRONLOCK}/${LOCKFILE} ]
   then
        touch ${ROGOCRONLOCK}/${LOCKFILE}
        cd ${ROGODIR}
        php ${ROGOSCRIPT} > ${LOGFILE} &
        rm -Rf ${ROGOCRONLOCK}/${LOCKFILE}
fi
