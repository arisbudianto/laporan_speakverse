import numpy as np, json
rng=np.random.default_rng(3)
N,K=80,8
theta=rng.normal(0,1,N); b=np.array([-2.5,-1,-0.5,0,0.3,0.8,1.5,3.0]); a=np.array([1.2,1.0,1.5,0.8,1.1,1.3,0.9,1.0])
pr=1/(1+np.exp(-a*(theta[:,None]-b)))
X=(rng.random((N,K))<pr).astype(int)
tot=X.sum(1)
# alpha
alpha=K/(K-1)*(1-X.var(0,ddof=1).sum()/tot.var(ddof=1))
rpb=[float(np.corrcoef(X[:,j],tot-X[:,j])[0,1]) for j in range(K)]
order=np.argsort(tot,kind='stable'); g=round(0.27*N)
D=[float(X[order[-g:],j].mean()-X[order[:g],j].mean()) for j in range(K)]
rows=[]
for i in range(N):
    for j in range(K):
        rows.append({'sub':i+1,'user':i+1,'form':'F','item':'i%d'%j,'option':('A' if X[i,j] else 'B'),'correct':int(X[i,j])})
print(json.dumps({'rows':rows,'alpha':float(alpha),'rpb':rpb,'p':X.mean(0).tolist(),'sem':float(tot.std(ddof=1)*np.sqrt(1-alpha)),'D_note':D}))
