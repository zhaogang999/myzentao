<?php
$config->task->edit->requiredFields      = 'name,type,fileNO,recorder,reviewName,referenceDoc,reference,pages,reviewers,reviewDate,reviewScope,reviewPlace,effort,conclusion,doc,auditID,noDec,noType,serious,cause,measures';
$config->task->finish->requiredFields    = 'consumed,file,fileNO,recorder,reviewName,referenceDoc,reference,pages,reviewers,reviewDate,reviewScope,reviewPlace,effort,conclusion,doc';
$config->task->create->newRequiredFields    = 'name,type,auditID,noDec,noType,serious,cause,measures';
//禅道任务增加关键字字段；需求：858 批量添加任务，批量编辑任务增加关键字字段
$config->task->customBatchCreateFields = 'module,story,assignedTo,estimate,estStarted,deadline,desc,pri,keywords';
$config->task->customBatchEditFields   = 'module,assignedTo,status,pri,estimate,record,left,estStarted,deadline,finishedBy,canceledBy,closedBy,closedReason,keywords,realStarted';