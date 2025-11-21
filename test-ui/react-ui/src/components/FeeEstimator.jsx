import { useEffect, useState } from "react";
import axios from "axios";

export default function FeeEstimator() {
  const [apiMap, setApiMap] = useState({});
  const [selectedService, setSelectedService] = useState("");
  const [selectedApi, setSelectedApi] = useState("");
  const [parameters, setParameters] = useState([]);
  const [values, setValues] = useState({});
  const [feeResult, setFeeResult] = useState(null);

  const backendUrlPrefix = import.meta.env.VITE_BACKEND_URL;

  useEffect(() => {
    axios.get(`${backendUrlPrefix}`)
      .then(res => setApiMap(res.data))
      .catch(err => console.error("Error loading API map:", err));
  }, []);

  useEffect(() => {
    if (!selectedApi) return;
    axios.get(`${backendUrlPrefix}/${selectedApi}/parameters`)
      .then(res => {
        const defaults = {};
        for (const param of res.data) {
          defaults[param.name] = param.defaultValue;
        }
        setParameters(res.data);
        setValues(defaults);
        setFeeResult(null);
      })
      .catch(err => console.error("Error loading parameters:", err));
  }, [selectedApi]);

  useEffect(() => {
    if (!selectedApi || Object.keys(values).length === 0) return;
    // Since we auto-calculate the values (there's no submit button), if the user
    // presses the delete key to enter a new value, we see a NaN.
    // Wait for a bit before triggering calculations
    const delayDebounceFn = setTimeout(() => {
      axios.post(`${backendUrlPrefix}/${selectedApi}/fee`, values)
        .then(res => setFeeResult(res.data))
        .catch(err => console.error("Fee computation failed:", err));
    }, 300);

    return () => clearTimeout(delayDebounceFn);
  }, [values, selectedApi]);

  const updateValue = (name, inputValue, type) => {
    let value = inputValue;
    if (type === 'number') {
      if (inputValue === "") {
        value = "";
      } else {
        const parsed = parseInt(inputValue, 10);
        value = isNaN(parsed) ? "" : parsed;
      }
    }
    setValues(prev => ({ ...prev, [name]: value }));
  };

  const getIncludedDefaults = (api) => {
    const defaults = [];
    
    // EntityCreate transactions - these set numFreeSignatures = numFreeKeys + 1
    if (api === "CryptoCreate" || api === "ConsensusCreateTopic" || api === "ScheduleCreate") {
      defaults.push("1 key");
      defaults.push("2 signatures"); // numFreeKeys (1) + 1
      return defaults;
    } else if (api === "TokenCreate") {
      defaults.push("7 keys");
      defaults.push("8 signatures"); // numFreeKeys (7) + 1
      return defaults;
    }
    
    // EntityUpdate transactions - default 1 signature
    if (api === "CryptoUpdate" || api === "ConsensusUpdateTopic" || api === "ContractUpdate") {
      defaults.push("1 key");
      defaults.push("1 signature");
      return defaults;
    } else if (api === "TokenUpdate") {
      defaults.push("7 keys");
      defaults.push("1 signature");
      return defaults;
    }
    
    // FileOperations
    if (api === "FileCreate" || api === "FileUpdate" || api === "FileAppend") {
      defaults.push("1 key");
      defaults.push("1000 bytes");
      defaults.push("1 signature");
      return defaults;
    }
    
    // ContractCreate - has 1 free key and 21,000 free gas, but only 1 signature (doesn't override setNumFreeSignatures)
    if (api === "ContractCreate") {
      defaults.push("1 key");
      defaults.push("21,000 gas");
      defaults.push("1 signature");
      return defaults;
    }
    
    // ContractBasedOnGas (ContractCall, EthereumTransaction) - no free gas (isMinGasFree = false)
    if (api === "ContractCall" || api === "EthereumTransaction") {
      defaults.push("1 signature");
      return defaults;
    }
    
    // ConsensusSubmitMessage
    if (api === "ConsensusSubmitMessage") {
      defaults.push("1024 bytes");
      defaults.push("1 signature");
      return defaults;
    }
    
    // CryptoTransfer/TokenTransfer/TokenAirdrop
    if (api === "CryptoTransfer" || api === "TokenTransfer" || api === "TokenAirdrop") {
      defaults.push("1 token");
      defaults.push("1 signature");
      return defaults;
    }
    
    // TokenMint
    if (api === "TokenMint") {
      defaults.push("1 token");
      defaults.push("1 signature");
      return defaults;
    }
    
    // TokenBurn - 1 free NFT (only for NFTs)
    if (api === "TokenBurn") {
      defaults.push("1 NFT");
      defaults.push("1 signature");
      return defaults;
    }
    
    // TokenWipe - 1 free NFT
    if (api === "TokenAccountWipe") {
      defaults.push("1 NFT");
      defaults.push("1 signature");
      return defaults;
    }
    
    // TokenAssociateToAccount/TokenDissociateFromAccount
    if (api === "TokenAssociateToAccount" || api === "TokenDissociateFromAccount") {
      defaults.push("1 token type");
      defaults.push("1 signature");
      return defaults;
    }
    
    // TokenAirdropOperations
    if (api === "TokenClaimAirdrop" || api === "TokenCancelAirdrop" || api === "TokenReject") {
      defaults.push("1 token type");
      defaults.push("1 signature");
      return defaults;
    }
    
    // TokenGetNftInfos
    if (api === "TokenGetNftInfos") {
      defaults.push("1 token");
      defaults.push("1 signature");
      return defaults;
    }
    
    // CryptoAllowance
    if (api === "CryptoApproveAllowance" || api === "CryptoDeleteAllowance") {
      defaults.push("1 allowance");
      defaults.push("1 signature");
      return defaults;
    }
    
    // LambdaSStore - no free items, just gas (no free gas)
    if (api === "LambdaSStore") {
      defaults.push("1 signature");
      return defaults;
    }
    
    // All other transactions (NoParametersAPI and others) - just 1 signature
    defaults.push("1 signature");
    return defaults;
  };

  return (
    <div className="min-h-screen bg-[#1c1c1c] text-white font-sans p-6">
      <div className="grid grid-cols-[20%_20%_1fr] gap-8">

        {/* STEP 1 */}
        <div>
          <h2 className="text-sm text-[#8c8c8c] uppercase tracking-widest mb-1 border-b border-indigo-500 pb-1">Step 1</h2>
          <p className="text-lg font-semibold mb-4">Select a <span className="text-white font-bold">Hedera service</span></p>
          <div className="flex flex-col gap-4">
            {Object.keys(apiMap).map(service => (
              <button
                key={service}
                className={`flex items-center gap-3 text-sm px-2 py-1 rounded transition-all duration-150 ${selectedService === service ? 'bg-indigo-700 text-white' : 'hover:bg-[#2a2a2a]'}`}
                onClick={() => {
                  setSelectedService(service);
                  setSelectedApi("");
                }}
              >
                <span className="text-left font-medium">{service.toUpperCase()} SERVICE</span>
              </button>
            ))}
          </div>
        </div>

        {/* STEP 2 */}
        <div>
          <h2 className="text-sm text-[#8c8c8c] uppercase tracking-widest mb-1 border-b border-indigo-500 pb-1">Step 2</h2>
          <p className="text-lg font-semibold mb-4">Select a <span className="text-white font-bold">Network API</span></p>
          <div className="flex flex-col gap-2 overflow-y-auto" style={{ maxHeight: 'calc(100vh - 220px)' }}>
            {selectedService && apiMap[selectedService]?.map(api => (
              <button
                key={api}
                className={`text-left px-2 py-1 rounded transition-all duration-150 ${selectedApi === api ? 'bg-indigo-700 text-white' : 'hover:bg-[#2a2a2a]'}`}
                onClick={() => setSelectedApi(api)}
              >
                {api}
              </button>
            ))}
          </div>
        </div>

        {/* STEP 3 */}
        <div>
          <h2 className="text-sm text-[#8c8c8c] uppercase tracking-widest mb-1 border-b border-indigo-500 pb-1">Step 3</h2>
          {selectedApi && (
            <>
              <p className="text-lg font-semibold mb-4">Enter the <span className="text-white font-bold">API call parameters</span> <span className="text-[#aaa]">({selectedApi})</span></p>
              <table className="w-full table-fixed text-sm">
                <tbody>
                  {parameters.reduce((rows, param, index) => {
                    if (index % 2 === 0) rows.push([param]);
                    else rows[rows.length - 1].push(param);
                    return rows;
                  }, []).map((row, rowIndex) => (
                    <tr key={rowIndex} className="align-bottom">
                      {row.map(param => (
                        <td key={param.name} className="p-2 w-1/2" style={{verticalAlign: "top"}}>
                          <div className="flex flex-col">
                            <label className="block text-[#ccc] mb-1">{param.prompt}</label>
                            {param.type === 'list' ? (
                              <select
                                value={values[param.name] ?? param.defaultValue}
                                onChange={(e) => updateValue(param.name, e.target.value)}
                                className="bg-[#2a2a2a] text-white border border-gray-600 rounded px-3 py-2"
                                style={{ borderRadius: "30px" }}
                              >
                                {param.values.map(opt => (
                                  <option key={opt} value={opt}>{opt}</option>
                                ))}
                              </select>
                            ) : (
                                <>
                                  <input
                                    type={param.type === 'boolean' ? 'checkbox' : 'text'}
                                    value={param.type === 'boolean' ? undefined : values[param.name] ?? ""}
                                    checked={param.type === 'boolean' ? values[param.name] || false : undefined}
                                    onChange={(e) => updateValue(param.name, param.type === 'boolean' ? e.target.checked : e.target.value, param.type)}
                                    className={`bg-[#2a2a2a] text-white border rounded px-3 py-2 ${param.type === 'number' && (values[param.name] < param.min || values[param.name] > param.max) ? 'border-red-500' : 'border-gray-600'}`}
                                    style={{ borderRadius: "30px" }}
                                  />
                                  <div className="h-4 mt-1">
                                    {param.type === 'number' && (values[param.name] < param.min || values[param.name] > param.max) && (
                                      <span className="text-red-400 text-xs">Must be between {param.min} and {param.max}</span>
                                    )}
                                  </div>
                                </>
                            )}
                          </div>
                        </td>
                      ))}
                      {row.length < 2 && <td className="w-1/2"></td>}
                    </tr>
                  ))}
                </tbody>
              </table>
            </>
          )}
        </div>
      </div>

      {/* FEE RESULT */}
      {feeResult && (
        <div className="fixed bottom-0 left-0 right-0 bg-gradient-to-r from-indigo-700 to-indigo-900 text-white p-4 shadow-inner mt-8">
          <div className="flex justify-center items-center">
            <div className="text-xl font-semibold">Charged Fee:</div>
            <div className="text-2xl font-bold ml-2">USD ${feeResult.fee.toFixed(5)}</div>
          </div>
          {feeResult.details && (
            <div className="mt-3">
              <div className="text-sm font-semibold text-gray-300 mb-1 text-center">Breakdown of fees</div>
              <div className="bg-[#2c2c2c] rounded-md p-3 mx-auto text-sm text-gray-300 max-w-6xl overflow-x-auto">
                <table className="w-full text-xs table-fixed">
                  <tbody>
                    {Object.entries(feeResult.details).reduce((rows, [label, detail], index) => {
                      const rowIndex = Math.floor(index / 3);
                      if (!rows[rowIndex]) rows[rowIndex] = [];
                      rows[rowIndex].push(
                        <td key={label} className="px-2 py-1 border border-gray-700 text-center w-1/3">
                          {label} (x{detail.value}): {detail.fee.toFixed(5)} USD
                        </td>
                      );
                      return rows;
                    }, []).map((row, i) => (
                      <tr key={i}>
                        {row}
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
              {/* Defaults included in base fee - All transactions */}
              {selectedApi && getIncludedDefaults(selectedApi).length > 0 && (
                <div className="mt-3">
                  <div className="text-sm font-semibold text-gray-300 mb-1 text-center">Included in base fee</div>
                  <div className="bg-[#2c2c2c] rounded-md p-3 mx-auto text-sm text-gray-300 max-w-6xl">
                    <div className="text-xs text-center">
                      {getIncludedDefaults(selectedApi).map((item, index) => (
                        <div key={index} className={index < getIncludedDefaults(selectedApi).length - 1 ? "mb-1" : ""}>
                          {item}
                        </div>
                      ))}
                    </div>
                  </div>
                </div>
              )}
            </div>
          )}
        </div>
      )}
    </div>
  );
}

