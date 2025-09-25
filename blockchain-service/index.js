require('dotenv').config();
const express = require('express');
const Web3 = require('web3').default;
const axios = require('axios');
const abi = require('./abi');

const app = express();
app.use(express.json());

// ────────────────────────────────────────────
//  Environment Validation
// ────────────────────────────────────────────
const requiredEnv = ['RPC_URL', 'PRIVATE_KEY', 'WALLET_ADDRESS', 'CONTRACT_ADDRESS', 'LARAVEL_API_URL'];
requiredEnv.forEach((key) => {
  if (!process.env[key]) {
    throw new Error(`Missing required environment variable: ${key}`);
  }
});

// ────────────────────────────────────────────
//  Web3 & Contract Setup
// ────────────────────────────────────────────
const web3 = new Web3(process.env.RPC_URL);
const contract = new web3.eth.Contract(abi, process.env.CONTRACT_ADDRESS);

const account = process.env.WALLET_ADDRESS;
const privateKey = process.env.PRIVATE_KEY.startsWith('0x')
  ? process.env.PRIVATE_KEY
  : `0x${process.env.PRIVATE_KEY}`;

// ────────────────────────────────────────────
//  Helper: sign & send contract method
// ────────────────────────────────────────────
async function sendTransaction(method, args) {
  const normalizedArgs = args.map(arg =>
    typeof arg === 'bigint' || typeof arg === 'number' ? arg.toString() : arg
  );

  const tx = contract.methods[method](...normalizedArgs);

  let gas;
  try {
    gas = await tx.estimateGas({ from: account });
    gas = Math.floor(Number(gas) * 1.2);
    console.log('Estimated gas:', gas);
  } catch (e) {
    console.warn('Gas estimation failed, fallback used. Error:', e.message);
    gas = 3000000;
  }

  const gasPrice = (await web3.eth.getGasPrice()).toString();
  const nonce = await web3.eth.getTransactionCount(account, 'pending');
  const data = tx.encodeABI();
  const chainId = await web3.eth.getChainId();

  const txObject = {
    from: account,
    to: process.env.CONTRACT_ADDRESS,
    gas: gas.toString(),
    gasPrice,
    nonce,
    data,
    chainId,
  };

  const signed = await web3.eth.accounts.signTransaction(txObject, privateKey);
  return await web3.eth.sendSignedTransaction(signed.rawTransaction);
}

// ────────────────────────────────────────────
//  Helper: log to Laravel API
// ────────────────────────────────────────────
async function logToLaravel({ caseId, txHash, action_type, payload }) {
  console.log('logToLaravel called with:', { caseId, txHash, action_type, payload });

  try {
    await axios.post(`${process.env.LARAVEL_API_URL}/api/blockchain-logs`, {
      case_id: caseId,
      tx_hash: txHash,
      action_type,
      payload: payload ? JSON.stringify(payload) : null
    });
    console.log('Synced to Laravel API');
  } catch (error) {
    console.error('Failed to sync with Laravel:', error.message);
  }

  console.log('Syncing to Laravel (end):', {
    case_id: caseId,
    tx_hash: txHash,
    action_type,
    payload: payload ? JSON.stringify(payload) : null,
  });
}

// ────────────────────────────────────────────
//  REST Endpoints
// ────────────────────────────────────────────
app.post('/log-case-transaction', async (req, res) => {
  const { caseId, txHash, payload } = req.body;  // Accept payload from client if any
  try {
    const receipt = await sendTransaction('logCaseTransaction', [caseId, txHash]);
    await logToLaravel({
      caseId,
      txHash: receipt.transactionHash,
      action_type: 'log_case_transaction',
      payload: payload || null  // pass payload if provided, else null
    });
    res.json({ success: true, tx: receipt.transactionHash });
  } catch (err) {
    console.error(err);
    res.status(500).json({ success: false, error: err.message });
  }
});

app.post('/log-assignment', async (req, res) => {
  const { caseId, assignmentHash, payload } = req.body;
  try {
    const receipt = await sendTransaction('logAssignmentHash', [caseId, assignmentHash]);
    await logToLaravel({
      caseId,
      txHash: receipt.transactionHash,
      action_type: 'log_assignment',
      payload: payload || null
    });
    res.json({ success: true, tx: receipt.transactionHash });
  } catch (err) {
    console.error(err);
    res.status(500).json({ success: false, error: err.message });
  }
});

app.post('/log-evidence', async (req, res) => {
  const { caseId, evidenceHash, payload } = req.body;
  try {
    const receipt = await sendTransaction('logEvidenceHash', [caseId, evidenceHash]);
    await logToLaravel({
      caseId,
      txHash: receipt.transactionHash,
      action_type: 'log_evidence',
      payload: payload || null
    });
    res.json({ success: true, tx: receipt.transactionHash });
  } catch (err) {
    console.error(err);
    res.status(500).json({ success: false, error: err.message });
  }
});

app.post('/log-case-closure', async (req, res) => {
  const { caseId, closureHash, payload } = req.body;
  try {
    const receipt = await sendTransaction('logCaseClosure', [caseId, closureHash]);
    await logToLaravel({
      caseId,
      txHash: receipt.transactionHash,
      action_type: 'log_case_closure',
      payload: payload || null
    });
    res.json({ success: true, tx: receipt.transactionHash });
  } catch (err) {
    console.error(err);
    res.status(500).json({ success: false, error: err.message });
  }
});

// ────────────────────────────────────────────
//  Start Server
// ────────────────────────────────────────────
const PORT = process.env.PORT || 3001;
app.listen(PORT, () =>
  console.log(` Blockchain service running on http://localhost:${PORT}`)
);
