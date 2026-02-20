// telemetry_agent.js
// Lê sensores do LibreHardwareMonitor (http://localhost:8085/data.json)
// e publica no seu servidor Laravel: POST /api/telemetry/push

const SERVER = 'http://localhost:8080/api/telemetry/push'; // ex: 
const TOKEN  = '54f8ffc1f0270e21859723f3bfe039a27879525e01372e235dcf22cb23ef2897';

const LHM_URL = 'http://localhost:8085/data.json';

async function getJson(url) {
  const res = await fetch(url);
  if (!res.ok) throw new Error('HTTP ' + res.status);
  return res.json();
}

// Procura sensores por nome/tipo. Ajuste as strings conforme o que o LHM exibe no seu PC
function findSensor(tree, predicate) {
  if (!tree) return null;
  if (Array.isArray(tree)) {
    for (const n of tree) {
      const f = findSensor(n, predicate);
      if (f) return f;
    }
  } else if (typeof tree === 'object') {
    if (predicate(tree)) return tree;
    if (tree.Children) {
      return findSensor(tree.Children, predicate);
    }
  }
  return null;
}

async function readSensors() {
  const data = await getJson(LHM_URL);

  const cpuTempNode = findSensor(data, n =>
    n.SensorType === 'Temperature' && /cpu package|cpu core/i.test(n.Name || n.Text || '')
  );
  const cpuLoadNode = findSensor(data, n =>
    n.SensorType === 'Load' && /cpu total/i.test(n.Name || n.Text || '')
  );
  const gpuTempNode = findSensor(data, n =>
    n.SensorType === 'Temperature' && /gpu core|gpu hotspot/i.test(n.Name || n.Text || '')
  );
  const pumpRpmNode = findSensor(data, n =>
    n.SensorType === 'Fan' && /pump|aio/i.test(n.Name || n.Text || '')
  );
  const coolantNode = findSensor(data, n =>
    n.SensorType === 'Temperature' && /liquid|coolant/i.test(n.Name || n.Text || '')
  );

  return {
    cpu_temp:     cpuTempNode ? Number(cpuTempNode.Value) : null,
    cpu_load:     cpuLoadNode ? Number(cpuLoadNode.Value) : null,
    gpu_temp:     gpuTempNode ? Number(gpuTempNode.Value) : null,
    pump_rpm:     pumpRpmNode ? Number(pumpRpmNode.Value) : null,
    coolant_temp: coolantNode ? Number(coolantNode.Value) : null,
  };
}

async function push(payload) {
  const res = await fetch(SERVER, {
    method: 'POST',
    headers: {
      'Authorization': 'Bearer ' + TOKEN,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(payload)
  });
  if (!res.ok) {
    const t = await res.text().catch(() => '');
    throw new Error('push failed: ' + res.status + ' ' + t);
  }
}

async function tick() {
  try {
    const sensors = await readSensors();
    await push(sensors);
    // console.log('sent', sensors);
  } catch (e) {
    // console.error(e);
  }
}

setInterval(tick, 10_000);
tick();
