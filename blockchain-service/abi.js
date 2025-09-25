const contractABI = [
  { "anonymous": false, "inputs": [ { "indexed": false, "internalType": "string", "name": "caseId", "type": "string" }, { "indexed": false, "internalType": "string", "name": "assignmentHash", "type": "string" }, { "indexed": false, "internalType": "uint256", "name": "timestamp", "type": "uint256" } ], "name": "AssignmentHashLogged", "type": "event" },
  { "anonymous": false, "inputs": [ { "indexed": false, "internalType": "string", "name": "caseId", "type": "string" }, { "indexed": false, "internalType": "string", "name": "closureHash", "type": "string" }, { "indexed": false, "internalType": "uint256", "name": "timestamp", "type": "uint256" } ], "name": "CaseClosureLogged", "type": "event" },
  { "anonymous": false, "inputs": [ { "indexed": false, "internalType": "string", "name": "caseId", "type": "string" }, { "indexed": false, "internalType": "string", "name": "txHash", "type": "string" }, { "indexed": false, "internalType": "uint256", "name": "timestamp", "type": "uint256" } ], "name": "CaseTransactionLogged", "type": "event" },
  { "anonymous": false, "inputs": [ { "indexed": false, "internalType": "string", "name": "caseId", "type": "string" }, { "indexed": false, "internalType": "string", "name": "evidenceHash", "type": "string" }, { "indexed": false, "internalType": "uint256", "name": "timestamp", "type": "uint256" } ], "name": "EvidenceHashLogged", "type": "event" },
  { "inputs": [ { "internalType": "string", "name": "_caseId", "type": "string" }, { "internalType": "string", "name": "_txHash", "type": "string" } ], "name": "logCaseTransaction", "outputs": [], "stateMutability": "nonpayable", "type": "function" },
  { "inputs": [ { "internalType": "string", "name": "_caseId", "type": "string" }, { "internalType": "string", "name": "_evidenceHash", "type": "string" } ], "name": "logEvidenceHash", "outputs": [], "stateMutability": "nonpayable", "type": "function" },
  { "inputs": [ { "internalType": "string", "name": "_caseId", "type": "string" }, { "internalType": "string", "name": "_assignmentHash", "type": "string" } ], "name": "logAssignmentHash", "outputs": [], "stateMutability": "nonpayable", "type": "function" },
  { "inputs": [ { "internalType": "string", "name": "_caseId", "type": "string" }, { "internalType": "string", "name": "_closureHash", "type": "string" } ], "name": "logCaseClosure", "outputs": [], "stateMutability": "nonpayable", "type": "function" }
];

module.exports = contractABI;
// This ABI is used to interact with the smart contract deployed on the blockchain.
// It includes functions to log case transactions, evidence hashes, assignment hashes, and case closures.
