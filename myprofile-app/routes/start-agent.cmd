@echo off
cd /d C:\telemetry-agent
set LHM_BASE=http://172.22.80.1:8085
set TELEMETRY_ENDPOINT=http://127.0.0.1:8080/api/telemetry/push
set TELEMETRY_TOKEN=54f8ffc1f0270e21859723f3bfe039a27879525e01372e235dcf22cb23ef2897
set POLL_MS=10000
node telemetry-agent.js
