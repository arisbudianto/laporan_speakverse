import numpy as np, json
from sklearn.linear_model import LogisticRegression
from sklearn.metrics import roc_auc_score, brier_score_loss
from scipy import stats
rng=np.random.default_rng(8)
n=120
X=rng.normal(0,1,(n,3)); z=0.4+1.3*X[:,0]-0.9*X[:,1]+0.0*X[:,2]
y=(rng.random(n)<1/(1+np.exp(-z))).astype(int)
mu=X.mean(0); sd=X.std(0,ddof=1); Xs=(X-mu)/sd
lam=2.0
m=LogisticRegression(C=1/lam,penalty='l2',solver='lbfgs',tol=1e-12,max_iter=10000).fit(Xs,y)
p=m.predict_proba(Xs)[:,1]
out={'X':X.tolist(),'y':y.tolist(),'lam':lam,'b0':float(m.intercept_[0]),'w':m.coef_[0].tolist(),'auc':float(roc_auc_score(y,p)),'brier':float(brier_score_loss(y,p)),'p':p.tolist()}
a=rng.normal(1.0,2.0,18); b=rng.normal(0.2,3.5,25); t=stats.ttest_ind(a,b,equal_var=False); ci=t.confidence_interval(0.95)
out['welch']={'a':a.tolist(),'b':b.tolist(),'t':float(t.statistic),'p':float(t.pvalue),'df':float(t.df),'ci':[float(ci.low),float(ci.high)],'diff':float(a.mean()-b.mean())}
# auc dengan ikatan
ys=rng.integers(0,2,40); sc=rng.integers(0,6,40).astype(float)
out['auc_ties']={'y':ys.tolist(),'s':sc.tolist(),'auc':float(roc_auc_score(ys,sc))}
print(json.dumps(out))
